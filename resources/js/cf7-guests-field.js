(function () {
  'use strict';

  function toInt(value, fallback) {
    var parsed = parseInt(value, 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  }

  function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
  }

  function setSelectOptions(select, min, max, selectedValue, placeholderText) {
    var html = '';
    var hasPlaceholder = typeof placeholderText === 'string' && placeholderText !== '';
    var selected = selectedValue === '' ? '' : clamp(selectedValue, min, max);

    if (hasPlaceholder) {
      html += '<option value=""' + (selected === '' ? ' selected' : '') + '>' + placeholderText + '</option>';
    }

    for (var i = min; i <= max; i++) {
      html += '<option value="' + i + '"' + (i === selected ? ' selected' : '') + '>' + i + '</option>';
    }

    select.innerHTML = html;
    select.value = selected === '' ? '' : String(selected);
  }

  function buildAgeFields(wrapper, count) {
    var ageBox = wrapper.querySelector('.cf7-guests-child-ages');
    if (!ageBox) return;

    var ageName = ageBox.getAttribute('data-age-name');
    var existing = Array.prototype.map.call(ageBox.querySelectorAll('input'), function (input) {
      return input.value;
    });

    ageBox.innerHTML = '';

    for (var i = 0; i < count; i++) {
      var row = document.createElement('div');
      row.className = 'cf7-guests-age-row';

      var label = document.createElement('label');
      label.textContent = 'Child ' + (i + 1) + ' age';

      var control = document.createElement('div');
      control.className = 'cf7-guests-control cf7-guests-control-age';

      var input = document.createElement('input');
      input.type = 'number';
      input.name = ageName;
      input.className = 'cf7-guests-child-age';
      input.min = '0';
      input.max = '17';
      input.placeholder = 'Children ' + (i + 1) + ' Age';
      input.required = true;
      input.setAttribute('aria-label', 'Child ' + (i + 1) + ' age');
      input.value = existing[i] || '';

      control.appendChild(input);
      row.appendChild(label);
      row.appendChild(control);
      ageBox.appendChild(row);
    }
  }

  function syncAgesVisibility(wrapper, count) {
    var agesSection = wrapper.querySelector('.cf7-guests-ages-section');
    if (!agesSection) return;

    agesSection.hidden = count === 0;
  }

  function setupGuestsField(wrapper) {
    var totalInput = wrapper.querySelector('.cf7-guests-total');
    var card = wrapper.querySelector('.cf7-guests-card');
    var panel = wrapper.querySelector('.cf7-guests-panel');
    var adultsInput = wrapper.querySelector('.cf7-guests-adults');
    var childrenInput = wrapper.querySelector('.cf7-guests-children');

    if (!totalInput || !card || !panel || !adultsInput || !childrenInput) return;

    var min = toInt(wrapper.getAttribute('data-min'), 0);
    var max = toInt(wrapper.getAttribute('data-max'), 20);
    var firstAsLabel = wrapper.getAttribute('data-first-as-label') === '1';
    var labelText = wrapper.getAttribute('data-label') || '';

    function syncFromTotal() {
      var rawValue = totalInput.value;
      var usePlaceholderSelection = firstAsLabel && rawValue === '';
      var total = clamp(toInt(rawValue, 0), min, max);
      setSelectOptions(totalInput, min, max, usePlaceholderSelection ? '' : total, firstAsLabel ? labelText : '');

      if (!usePlaceholderSelection && total > 0) {
        card.hidden = false;
        panel.hidden = false;
      } else {
        card.hidden = true;
        panel.hidden = true;
      }

      var adults = clamp(toInt(adultsInput.value, total), 0, total);
      var children = clamp(toInt(childrenInput.value, 0), 0, total);

      if (adults + children !== total) {
        adults = total;
        children = 0;
      }

      setSelectOptions(adultsInput, 0, total, adults);
      setSelectOptions(childrenInput, 0, total, children);
      syncAgesVisibility(wrapper, children);
      buildAgeFields(wrapper, children);
    }

    function syncSplit(changed) {
      var total = clamp(toInt(totalInput.value, 0), min, max);
      var adults = clamp(toInt(adultsInput.value, 0), 0, total);
      var children = clamp(toInt(childrenInput.value, 0), 0, total);

      if (changed === 'adults') {
        children = total - adults;
      } else {
        adults = total - children;
      }

      adultsInput.value = adults;
      childrenInput.value = children;
      syncAgesVisibility(wrapper, children);
      buildAgeFields(wrapper, children);
    }

    totalInput.addEventListener('change', syncFromTotal);
    adultsInput.addEventListener('change', function () { syncSplit('adults'); });
    childrenInput.addEventListener('change', function () { syncSplit('children'); });

    syncFromTotal();
  }

  function init() {
    document.querySelectorAll('.cf7-guests-field').forEach(setupGuestsField);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
