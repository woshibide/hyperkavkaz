"""Run with Blender: blender -b emblem.blend --python scripts/export-emblem.py -- reference.jpeg."""

import sys
import tempfile
from itertools import product
from pathlib import Path

import bpy
import numpy as np
from mathutils import Matrix


# These identifiers belong to the four faces. Their ornaments share the same hit target.
PARTS = {
    "MAIN_SHIELD": ("leather", 8000, None),
    "OUTER_FRAME": ("rim", 5000, None),
    "METAL_IN_THE_CENTER": ("chrome", 11000, None),
    "HYPER": ("chrome", 8000, None),
    "KAVKAZ": ("chrome", 8000, None),
    "STARS": ("chrome", 1500, None),
    "LEFT_TOP_PANEL": ("red", 3000, "top-left"),
    "RIGHT_TOP_PANEL": ("engraving", 3000, "top-right"),
    "BOTTOM_LEFT_PANEL": ("silver", 3000, "bottom-left"),
    "BOTTOM_RIGHT_PANEL": ("red", 3000, "bottom-right"),
    "CHECHEN": ("chrome", 3500, "top-left"),
    "ADIDAS": ("stripes", 2000, "bottom-left"),
    "EAGLE": ("red-relief", 5000, "bottom-left"),
    "ARMENIAN_ORNAMENT": ("chrome", 3500, "bottom-right"),
}


def linear_color(hex_color):
    rgb = [int(hex_color[i:i + 2], 16) / 255 for i in (0, 2, 4)]
    return (*[c / 12.92 if c < 0.04045 else ((c + 0.055) / 1.055) ** 2.4 for c in rgb], 1)


def material(name, color, metalness, roughness, coat=0):
    surface = bpy.data.materials.new(name)
    shader = surface.node_tree.nodes.get("Principled BSDF")
    shader.inputs["Base Color"].default_value = linear_color(color)
    shader.inputs["Metallic"].default_value = metalness
    shader.inputs["Roughness"].default_value = roughness
    shader.inputs["Coat Weight"].default_value = coat
    shader.inputs["Coat Roughness"].default_value = 0.2
    return surface


def image_node(surface, name, pixels, directory, color_space):
    height, width, _ = pixels.shape
    image = bpy.data.images.new(name, width, height, alpha=False)
    image.colorspace_settings.name = color_space
    image.pixels.foreach_set(pixels.astype(np.float32).ravel())
    image.filepath_raw = str(directory / f"{name}.png")
    image.file_format = "PNG"
    image.save()
    image.pack()
    texture = surface.node_tree.nodes.new("ShaderNodeTexImage")
    texture.image = image
    return texture


def leather_grain():
    """Generate a seamless normal map from rounded, irregular leather pebbles."""
    resolution, cells = 256, 13
    rng = np.random.default_rng(580)
    sites = rng.uniform(0.12, 0.88, (cells, cells, 2))
    coords = np.arange(resolution) * cells / resolution
    x, y = np.meshgrid(coords, coords)
    x += 0.13 * np.sin(y * 6 * np.pi / cells)
    y += 0.18 * np.sin(x * 8 * np.pi / cells)
    cell_x, cell_y = np.floor(x).astype(int), np.floor(y).astype(int)
    nearest = np.full(x.shape, np.inf)
    second = nearest.copy()

    for dx, dy in product((-1, 0, 1), repeat=2):
        ix, iy = cell_x + dx, cell_y + dy
        sx, sy = np.moveaxis(sites[iy % cells, ix % cells], -1, 0)
        distance = (x - ix - sx) ** 2 + (y - iy - sy) ** 2
        second = np.minimum(second, np.maximum(nearest, distance))
        nearest = np.minimum(nearest, distance)

    seam = np.sqrt(second) - np.sqrt(nearest)
    height = 0.65 * (1 - np.exp(-8 * seam)) + 0.35 * np.exp(-5 * nearest)
    dx = np.roll(height, 1, axis=1) - np.roll(height, -1, axis=1)
    dy = np.roll(height, 1, axis=0) - np.roll(height, -1, axis=0)
    normals = np.stack((1.8 * dx, 1.8 * dy, np.ones_like(dx)), axis=-1)
    normals /= np.linalg.norm(normals, axis=-1, keepdims=True)
    return np.dstack((normals * 0.5 + 0.5, np.ones_like(dx)))


def engraving(reference):
    """Project the upper-right motif from the reference, isolating its dark ink."""
    image = bpy.data.images.load(str(reference))
    width, height = image.size
    pixels = np.empty(width * height * 4, dtype=np.float32)
    image.pixels.foreach_get(pixels)
    pixels = pixels.reshape((height, width, 4))
    u, v = np.meshgrid(np.linspace(0, 1, 512), np.linspace(0, 1, 512))
    x, y = 232 + u * 112, 330 - v * 114
    sample = pixels[
        np.round((1 - y / 650) * (height - 1)).astype(int),
        np.round(x / 439 * (width - 1)).astype(int),
        :3,
    ].mean(axis=-1)
    # The arched chrome border sits just outside the enamel.
    inside = np.clip((y - (214 + 6 * u + 24 * u ** 2)) / 2, 0, 1)
    ink = np.clip((0.72 - sample) / 0.52, 0, 1) * inside
    grey = 0.84 - 0.79 * ink ** 1.35
    return np.dstack((grey, grey, grey, np.ones_like(grey)))


def projected_uv(obj, repeats=None):
    mesh = obj.data
    coords = np.array([vertex.co for vertex in mesh.vertices])[:, (0, 2)]
    coords -= coords.min(axis=0)
    coords /= coords.max(axis=0)
    if repeats:
        coords *= repeats
    uv = mesh.uv_layers.new(name="Surface UV")
    indices = np.empty(len(mesh.loops), dtype=np.int32)
    mesh.loops.foreach_get("vertex_index", indices)
    uv.uv.foreach_set("vector", coords[indices].astype(np.float32).ravel())


def export(reference, output, directory):
    surfaces = {
        "leather": material("Black pebbled leather", "181719", 0, 0.54, 0.15),
        "rim": material("Outer chrome edge", "b1b6c1", 0.95, 0.22),
        "chrome": material("Polished chrome", "c6c9ce", 0.96, 0.21),
        "red": material("Crimson enamel", "b0061e", 0.28, 0.25, 0.4),
        "silver": material("Silver enamel", "d5d5d8", 0.26, 0.27, 0.35),
        "engraving": material("Engraved silver enamel", "d5d5d8", 0.22, 0.33, 0.22),
        "stripes": material("Blackened metal", "202026", 0.54, 0.28),
        "red-relief": material("Polished red relief", "d51b2b", 0.47, 0.23, 0.35),
    }
    leather = surfaces["leather"]
    grain = image_node(leather, "leather-grain", leather_grain(), directory, "Non-Color")
    bump = leather.node_tree.nodes.new("ShaderNodeNormalMap")
    bump.inputs["Strength"].default_value = 0.54
    leather.node_tree.links.new(grain.outputs["Color"], bump.inputs["Color"])
    leather.node_tree.links.new(bump.outputs["Normal"], leather.node_tree.nodes.get("Principled BSDF").inputs["Normal"])

    engraved = surfaces["engraving"]
    ink = image_node(engraved, "engraved-ornament", engraving(reference), directory, "sRGB")
    ink.extension = "EXTEND"
    engraved.node_tree.links.new(ink.outputs["Color"], engraved.node_tree.nodes.get("Principled BSDF").inputs["Base Color"])

    transforms = {name: bpy.context.scene.objects[name].matrix_world.copy() for name in PARTS}
    bpy.ops.object.select_all(action="DESELECT")
    for name, (surface, budget, panel) in PARTS.items():
        obj = bpy.context.scene.objects[name]
        obj.animation_data_clear()
        obj.parent = None
        obj.matrix_world = Matrix.Identity(4)
        obj.data.transform(transforms[name])
        obj.data.materials.clear()
        obj.data.materials.append(surfaces[surface])
        for uv in list(obj.data.uv_layers):
            obj.data.uv_layers.remove(uv)
        if surface == "leather":
            projected_uv(obj, repeats=(8, 10))
        if surface == "engraving":
            projected_uv(obj)

        if panel:
            obj["panel"] = panel

        triangles = sum(len(face.vertices) - 2 for face in obj.data.polygons)
        if triangles > budget:
            modifier = obj.modifiers.new("Web triangle budget", "DECIMATE")
            modifier.ratio = budget / triangles
            modifier.use_collapse_triangulate = True
        obj.select_set(True)

    output.parent.mkdir(parents=True, exist_ok=True)
    bpy.ops.export_scene.gltf(
        filepath=str(output), use_selection=True, export_apply=True,
        export_extras=True, export_animations=False,
    )


if __name__ == "__main__":
    reference = Path(sys.argv[sys.argv.index("--") + 1]).resolve()
    output = Path(__file__).resolve().parents[1] / "assets/models/emblem.glb"
    with tempfile.TemporaryDirectory() as temp:
        export(reference, output, Path(temp))
