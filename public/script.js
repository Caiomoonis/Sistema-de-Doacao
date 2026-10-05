(() => {

  const modal = document.getElementById('signupModal');
  const openers = ['signupBtn', 'heroSignup', 'ctaSignup'];
  const form = document.getElementById('signupForm');
  const msg = document.getElementById('formMsg');

  const loginModal = document.getElementById('loginModal');
  const loginForm = document.getElementById('loginForm');
  const loginMsg = document.getElementById('loginMsg');

  // =====================
  // MODAL CADASTRO
  // =====================
  const open = () => {
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
  };

  const close = () => {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    msg.textContent = '';
    msg.className = 'form-msg';
  };

  openers.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('click', open);
  });

  modal.querySelectorAll('[data-close]')
    .forEach(el => el.addEventListener('click', close));

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') close();
  });

  // =====================
  // LOGIN MODAL
  // =====================
  const openLogin = () => {
    loginModal.classList.add('open');
    loginModal.setAttribute('aria-hidden', 'false');
  };

  const closeLogin = () => {
    loginModal.classList.remove('open');
    loginModal.setAttribute('aria-hidden', 'true');
    loginMsg.textContent = '';
    loginMsg.className = 'form-msg';
  };

  document.getElementById('loginBtn')
    ?.addEventListener('click', openLogin);

  loginModal.querySelectorAll('[data-close-login]')
    .forEach(el => el.addEventListener('click', closeLogin));

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      closeLogin();
      close();
    }
  });

  // =====================
  // LOGIN SUBMIT (CORRIGIDO)
  // =====================
  loginForm?.addEventListener('submit', function (e) {

    const tipo = document.querySelector('input[name="tipo"]:checked');

    if (!tipo) {
      e.preventDefault();
      loginMsg.textContent = 'Escolha um perfil (Doador ou Cliente)';
      loginMsg.className = 'form-msg error';
      return;
    }

    // PADRONIZAÇÃO IMPORTANTE:
    // recebedor = cliente
    const tipoUsuario = (tipo.value === 'doador') ? 'doador' : 'cliente';

    this.action = tipoUsuario === 'doador'
      ? '/doador/autenticar'
      : '/cliente/autenticar';

    this.method = 'POST';
  });

  // =====================
  // CADASTRO SUBMIT
  // =====================
  form?.addEventListener('submit', function (e) {

    const tipo = document.querySelector('input[name="tipo"]:checked');

    if (!tipo) {
      e.preventDefault();
      msg.textContent = 'Escolha um perfil';
      msg.className = 'form-msg error';
      return;
    }

    // PADRONIZAÇÃO: recebedor = cliente
    const tipoUsuario = (tipo.value === 'doador') ? 'doador' : 'cliente';

    this.action = tipoUsuario === 'doador'
      ? '/doador/salvar'
      : '/cliente/salvar';

    this.method = 'POST';
  });

  // =====================
  // SCROLL SUAVE
  // =====================
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const id = a.getAttribute('href').slice(1);
      const target = document.getElementById(id);

      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

})();