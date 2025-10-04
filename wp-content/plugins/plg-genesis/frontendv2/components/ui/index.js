// UI Kit mínimo reutilizable (vanilla JS)

export function createButton({ label = '', variant = 'primary', onClick, attrs = {} } = {}) {
	const btn = document.createElement('button');
	btn.className = 'btn' + (variant ? ` btn-${variant}` : '');
	btn.textContent = label;
	if (onClick) btn.addEventListener('click', onClick);
	Object.entries(attrs).forEach(([k, v]) => btn.setAttribute(k, v));
	return btn;
}

export function createInput({ type = 'text', placeholder = '', value = '', invalid = false, attrs = {} } = {}) {
	const input = document.createElement('input');
	input.type = type;
	input.className = 'input' + (invalid ? ' invalid' : '');
	if (placeholder) input.placeholder = placeholder;
	if (value !== undefined && value !== null) input.value = value;
	Object.entries(attrs).forEach(([k, v]) => input.setAttribute(k, v));
	return input;
}

export function createSelect({ options = [], value = '', placeholder = 'Seleccione…', attrs = {} } = {}) {
	const select = document.createElement('select');
	select.className = 'input';
	const opts = [placeholder, ...options];
	opts.forEach((opt, idx) => {
		const o = document.createElement('option');
		o.value = idx === 0 ? '' : String(opt);
		o.textContent = idx === 0 ? placeholder : String(opt);
		select.appendChild(o);
	});
	select.value = value || '';
	Object.entries(attrs).forEach(([k, v]) => select.setAttribute(k, v));
	return select;
}

export function createTable({ columns = [], rows = [] } = {}) {
	const table = document.createElement('table');
	table.className = 'table';
	const thead = document.createElement('thead');
	const trh = document.createElement('tr');
    columns.forEach(c => {
		const th = document.createElement('th');
		th.textContent = c;
        th.scope = 'col';
		trh.appendChild(th);
	});
	thead.appendChild(trh);
	const tbody = document.createElement('tbody');
	if (rows.length === 0) {
		const tr = document.createElement('tr');
		const td = document.createElement('td');
		td.colSpan = Math.max(columns.length, 1);
		td.textContent = 'Sin resultados';
		tr.appendChild(td);
		tbody.appendChild(tr);
	} else {
		rows.forEach(row => {
			const tr = document.createElement('tr');
			row.forEach(cell => {
				const td = document.createElement('td');
				td.textContent = cell == null ? '' : String(cell);
				tr.appendChild(td);
			});
			tbody.appendChild(tr);
		});
	}
	table.appendChild(thead);
	table.appendChild(tbody);
	return table;
}

export function createCard({ title = '', content } = {}) {
	const card = document.createElement('div');
	card.className = 'card';
	if (title) {
		const h = document.createElement('div');
		h.className = 'card-title';
		h.textContent = title;
		card.appendChild(h);
	}
	if (content instanceof Node) card.appendChild(content);
	else if (typeof content === 'string') {
		const wrap = document.createElement('div');
		wrap.innerHTML = content;
		card.appendChild(wrap);
	}
	return card;
}

export function showToast(text, isError = false) {
	const t = document.createElement('div');
	t.className = 'toast';
	if (isError) t.style.borderColor = 'var(--plg-danger)';
	t.textContent = text;
    t.setAttribute('role', isError ? 'alert' : 'status');
    t.setAttribute('aria-live', isError ? 'assertive' : 'polite');
	document.body.appendChild(t);
	setTimeout(() => { t.remove(); }, 2500);
}

// Detail helpers
export function createDetailGrid(children = []) {
    const wrap = document.createElement('div');
    wrap.className = 'detail-grid';
    children.forEach(ch => { if (ch) wrap.appendChild(ch); });
    return wrap;
}

export function createFieldView({ label = '', value = '', span = 1 } = {}) {
    const box = document.createElement('div');
    box.className = 'field-view';
    if (span > 1) box.style.gridColumn = `span ${span}`;
    const l = document.createElement('div'); l.className = 'field-label'; l.textContent = label;
    const v = document.createElement('div'); v.className = 'field-value'; v.textContent = (value ?? '') || '-';
    box.appendChild(l); box.appendChild(v);
    return box;
}

export default {
	createButton,
	createInput,
	createSelect,
	createTable,
	createCard,
    showToast,
    createDetailGrid,
    createFieldView,
};


