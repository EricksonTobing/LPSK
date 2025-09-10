// Debounce helper (untuk auto submit search)
window.debounce = function (fn, d = 1000) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), d);
  };
};
