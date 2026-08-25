(function () {
  const list = document.getElementById('fm-buttons-list');
  const addBtn = document.getElementById('fm-add-btn');
  const template = document.getElementById('fm-row-template');

  if (!list || !addBtn || !template) {
    return;
  }

  function nextIndex() {
    let max = -1;
    list.querySelectorAll('.fm-row').forEach((row) => {
      const index = parseInt(row.dataset.index, 10);
      if (!Number.isNaN(index) && index > max) {
        max = index;
      }
    });
    return max + 1;
  }

  function bindRow(row) {
    const typeSelect = row.querySelector('.fm-type');
    const valueInput = row.querySelector('.fm-value');
    const labelInput = row.querySelector('.fm-label');
    const colorInput = row.querySelector('.fm-color');
    const hint = row.querySelector('.fm-hint');
    const title = row.querySelector('.fm-row__title');
    const removeBtn = row.querySelector('.fm-remove-btn');

    function syncTypeMeta(updateColor) {
      const option = typeSelect.options[typeSelect.selectedIndex];
      if (!option) {
        return;
      }

      valueInput.placeholder = option.dataset.placeholder || '';
      if (hint) {
        hint.textContent = option.dataset.hint || '';
      }

      if (updateColor && option.dataset.color) {
        colorInput.value = option.dataset.color;
      }

      if (!labelInput.value) {
        title.textContent = option.dataset.label || 'Кнопка';
      }
    }

    typeSelect.addEventListener('change', () => {
      if (!labelInput.value) {
        labelInput.placeholder = typeSelect.options[typeSelect.selectedIndex].dataset.label || '';
      }
      syncTypeMeta(true);
    });

    labelInput.addEventListener('input', () => {
      title.textContent = labelInput.value || typeSelect.options[typeSelect.selectedIndex].dataset.label || 'Кнопка';
    });

    removeBtn.addEventListener('click', () => {
      if (list.querySelectorAll('.fm-row').length <= 1) {
        valueInput.value = '';
        labelInput.value = '';
        return;
      }
      row.remove();
    });

    syncTypeMeta(false);
  }

  list.querySelectorAll('.fm-row').forEach(bindRow);

  addBtn.addEventListener('click', () => {
    const index = nextIndex();
    const html = template.innerHTML.replaceAll('__INDEX__', String(index));
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html.trim();
    const row = wrapper.firstElementChild;
    list.appendChild(row);
    bindRow(row);
  });
})();
