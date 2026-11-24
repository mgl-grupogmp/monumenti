const message = document.querySelector('textarea[name="mensaje"]');
if (message) {
  const family = new URLSearchParams(location.search).get('pieza');
  if (family && !message.value) message.value = 'Me interesa la familia «' + family.slice(0, 100) + '». ';
}
