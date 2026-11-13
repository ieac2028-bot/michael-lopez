// Starter-suggestion chips on the consultation page: fill (never overwrite) the textarea.
(function () {
  var field = document.getElementById('opening-message');
  if (!field) return;
  document.querySelectorAll('.cp-chip[data-fill]').forEach(function (chip) {
    chip.addEventListener('click', function () {
      var text = chip.getAttribute('data-fill') || '';
      var current = field.value.trim();
      field.value = current ? current + ' ' + text : text;
      field.focus();
      field.setSelectionRange(field.value.length, field.value.length);
    });
  });
})();
