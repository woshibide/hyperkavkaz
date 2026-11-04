"""Render the GitHub Pages preview with Kirby. Run after composer install."""

import http.client
import json
import os
import re
import shutil
import socket
import subprocess
import tempfile
import time
from collections import deque
from html.parser import HTMLParser
from pathlib import Path, PurePosixPath
from urllib.parse import quote, unquote, urljoin, urlsplit

ROOT = Path(__file__).resolve().parents[1]
OUTPUT = ROOT / "_preview"
HOST = "woshibide.github.io"
PREFIX = "/hyperkavkaz/"
ORIGIN = f"https://{HOST}"
ASSET_ROOTS = ("assets/", "media/pages/", "media/site/")
ASSET_TYPES = {
    ".css", ".js", ".svg", ".png", ".jpg", ".jpeg", ".webp", ".avif", ".gif",
    ".ico", ".pdf", ".zip", ".woff", ".woff2", ".mp3", ".mp4", ".webm", ".glb",
}
MODULE_IMPORT = re.compile(
    r"""^\s*(?:import\s+(?:[^;'"]*?\s+from\s+)?|export\s+[^;'"]*?\s+from\s+)['"]([^'"]+)['"]""",
    re.MULTILINE,
)
CSS_URL = re.compile(r"""url\(\s*['"]?([^'"\s)]+)""")


class PageLinks(HTMLParser):
    def __init__(self, html):
        super().__init__()
        self.links = []
        self.imports = {}
        self.in_importmap = False
        self.feed(html)

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        for name in ("href", "src", "data-src", "data-bump-src", "poster"):
            if attrs.get(name):
                self.links.append(attrs[name])
        if attrs.get("srcset"):
            self.links.extend(item.strip().split()[0] for item in attrs["srcset"].split(",") if item.strip())
        self.in_importmap = tag == "script" and attrs.get("type") == "importmap"

    def handle_endtag(self, tag):
        if tag == "script":
            self.in_importmap = False

    def handle_data(self, data):
        if self.in_importmap:
            self.imports.update(json.loads(data)["imports"])


def local_path(reference, source):
    if reference.startswith("#"):
        return None
    url = urlsplit(urljoin(ORIGIN + source, reference))
    if url.hostname in {"127.0.0.1", "localhost", "::1"}:
        raise ValueError(f"{source}: preview link points to loopback: {reference}")
    if url.hostname != HOST:
        return None
    if url.path == PREFIX.rstrip("/"):
        return ""
    if not url.path.startswith(PREFIX):
        raise ValueError(f"{source}: {reference} escapes {PREFIX}")
    path = unquote(url.path.removeprefix(PREFIX))
    if any(part.startswith(".") for part in PurePosixPath(path).parts) or "\\" in path or path.startswith("/"):
        raise ValueError(f"{source}: unsafe preview path {reference}")
    return path


def fetch(port, path, expected=200):
    connection = http.client.HTTPConnection("127.0.0.1", port, timeout=15)
    try:
        connection.request("GET", PREFIX + quote(path, safe="/"), headers={"Host": HOST})
        response = connection.getresponse()
        body = response.read()
        if response.status != expected:
            raise ValueError(f"{path or '/'} returned {response.status}, expected {expected}")
        return body
    finally:
        connection.close()


def export(port, output, page_ids):
    pages = deque(["", *page_ids, "404.html"])
    assets = deque()
    seen_pages = set()
    seen_assets = set()
    imports = {}

    while pages:
        path = pages.popleft().rstrip("/")
        if path in seen_pages:
            continue
        seen_pages.add(path)
        missing = path == "404.html"
        html = fetch(port, path, expected=404 if missing else 200)
        links = PageLinks(html.decode("utf-8"))
        imports.update(links.imports)

        destination = output / ("404.html" if missing else f"{path + '/' if path else ''}index.html")
        destination.parent.mkdir(parents=True, exist_ok=True)
        destination.write_bytes(html)
        for reference in [*links.links, *(value for key, value in links.imports.items() if not key.endswith("/"))]:
            target = local_path(reference, PREFIX + quote(path, safe="/"))
            if target is None:
                continue
            if target.startswith(ASSET_ROOTS):
                assets.append(target)
            elif target.rstrip("/") in page_ids or target == "":
                pages.append(target)
            else:
                raise ValueError(f"{path or '/'}: unexpected local link {reference}")

    while assets:
        path = assets.popleft()
        if path in seen_assets:
            continue
        seen_assets.add(path)
        suffix = PurePosixPath(path).suffix.lower()
        if not path.startswith(ASSET_ROOTS) or suffix not in ASSET_TYPES:
            raise ValueError(f"Forbidden preview asset: {path}")
        source = ROOT / path
        public_root = ROOT / PurePosixPath(path).parts[0]
        if not source.resolve().is_relative_to(public_root):
            raise ValueError(f"Asset outside {public_root.name}: {path}")
        # Kirby only serves a thumbnail through its router while a generation job exists.
        if path.startswith("assets/") or source.is_file():
            body = source.read_bytes()
        else:
            body = fetch(port, path)

        destination = output / path
        destination.parent.mkdir(parents=True, exist_ok=True)
        destination.write_bytes(body)

        source_url = PREFIX + quote(path, safe="/")
        references = []
        if suffix == ".js":
            for name in MODULE_IMPORT.findall(body.decode("utf-8")):
                if name.startswith((".", "/")):
                    references.append(name)
                elif name in imports:
                    references.append(imports[name])
                else:
                    prefix = next((key for key in imports if key.endswith("/") and name.startswith(key)), None)
                    if prefix is None:
                        raise ValueError(f"{path}: unknown module import {name}")
                    references.append(imports[prefix] + name.removeprefix(prefix))
        elif suffix == ".css":
            references = CSS_URL.findall(body.decode("utf-8"))

        for reference in references:
            target = local_path(reference, source_url)
            if target is not None:
                assets.append(target)

    print(f"Exported {len(seen_pages)} pages and {len(seen_assets)} assets.")


def main():
    env = {**os.environ, "HYPERKAVKAZ_PREVIEW": "1", "HYPERKAVKAZ_HOLDING_PAGE": "0"}
    page_ids = json.loads(subprocess.check_output([
        "php", "-r",
        """require 'kirby/bootstrap.php';
        $kirby = new Kirby;
        $pages = $kirby->site()->index()->filter(fn ($page) => !$page->isHomePage() && !$page->isErrorPage());
        echo json_encode(array_values($pages->pluck('id')), JSON_THROW_ON_ERROR);""",
    ], cwd=ROOT, env=env))
    for path in page_ids:
        if local_path(PREFIX + quote(path, safe="/"), PREFIX) != path:
            raise ValueError(f"Unsafe Kirby page ID: {path}")

    with tempfile.TemporaryDirectory(prefix=".preview-", dir=ROOT) as temp:
        temp = Path(temp)
        with socket.socket() as listener:
            listener.bind(("127.0.0.1", 0))
            port = listener.getsockname()[1]

        with (temp / "server.log").open("w+") as log:
            server = subprocess.Popen([
                "php", "-S", f"127.0.0.1:{port}", "-t", str(temp), str(ROOT / "scripts/preview-router.php"),
            ], cwd=ROOT, env=env, stdout=log, stderr=log)
            try:
                for _ in range(100):
                    try:
                        with socket.create_connection(("127.0.0.1", port), timeout=0.1):
                            break
                    except OSError:
                        if server.poll() is not None:
                            raise RuntimeError("Kirby preview server exited")
                        time.sleep(0.05)
                else:
                    raise RuntimeError("Kirby preview server did not start")
                export(port, temp / "site", page_ids)
            except Exception:
                log.seek(0)
                print(log.read())
                raise
            finally:
                if server.poll() is None:
                    server.terminate()
                server.wait(timeout=5)

        if OUTPUT.exists():
            shutil.rmtree(OUTPUT)
        (temp / "site").rename(OUTPUT)


if __name__ == "__main__":
    main()
