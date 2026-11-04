const blog = document.querySelector('[data-blog]');
const tabs = blog.querySelector('.tabs');
const categories = [...tabs.querySelectorAll('[data-category]')];
const posts = [...blog.querySelectorAll('[data-post-category]')];
const pagination = blog.querySelector('.pagination');
const empty = blog.querySelector('.empty');
const pageSize = 10;

function pageUrl(category, number) {
  const url = new URL(categories[0].href);
  if (category) url.searchParams.set('category', category);
  if (number > 1) url.searchParams.set('page', number);
  return url;
}

function pageLink(label, category, number) {
  const link = document.createElement('a');
  link.textContent = label;
  link.href = pageUrl(category, number);
  return link;
}

function neighbor(label, category, number, available, relation) {
  if (available) {
    const link = pageLink(label, category, number);
    link.rel = relation;
    return link;
  }

  const placeholder = document.createElement('span');
  placeholder.className = 'pagination-placeholder';
  placeholder.setAttribute('aria-hidden', 'true');
  placeholder.textContent = label;
  return placeholder;
}

function render() {
  const params = new URLSearchParams(location.search);
  const selected = categories.find(link => link.dataset.category === params.get('category')) ?? categories[0];
  const category = selected.dataset.category;
  const matches = posts.filter(post => !category || post.dataset.postCategory === category);
  const lastPage = Math.max(1, Math.ceil(matches.length / pageSize));
  const requested = params.get('page') ?? '';
  const number = /^[1-9][0-9]*$/.test(requested) ? Math.min(Number(requested), lastPage) : 1;
  const offset = (number - 1) * pageSize;
  const visible = new Set(matches.slice(offset, offset + pageSize));

  for (const link of categories) {
    if (link === selected) link.setAttribute('aria-current', 'page');
    else link.removeAttribute('aria-current');
  }

  for (const post of posts) post.hidden = !visible.has(post);
  empty.textContent = category ? `No posts in ${selected.textContent} yet.` : 'No posts yet.';
  empty.hidden = matches.length > 0;

  if (!pagination) return;
  pagination.hidden = lastPage <= 1;
  if (pagination.hidden) return;

  const numbers = document.createElement('ul');
  const first = Math.max(1, Math.min(number - 2, lastPage - 4));
  for (let page = first; page <= Math.min(lastPage, first + 4); page++) {
    const item = document.createElement('li');
    const link = pageLink(page, category, page);
    link.setAttribute('aria-label', `Page ${page}`);
    if (page === number) link.setAttribute('aria-current', 'page');
    item.append(link);
    numbers.append(item);
  }

  pagination.replaceChildren(
    neighbor('Previous', category, number - 1, number > 1, 'prev'),
    numbers,
    neighbor('Next', category, number + 1, number < lastPage, 'next'),
  );
}

blog.addEventListener('click', event => {
  const link = event.target.closest('.tabs a, .pagination a');
  if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

  event.preventDefault();
  const movedFromPagination = pagination?.contains(link);
  history.pushState(null, '', link.href);
  render();
  if (movedFromPagination) pagination.querySelector('[aria-current="page"]')?.focus();
});

window.addEventListener('popstate', render);
render();
tabs.hidden = false;
