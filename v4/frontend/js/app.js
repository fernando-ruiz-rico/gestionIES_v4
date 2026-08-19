(function (window, document, Vue) {
  'use strict';

  var backendBase = '../backend/';
  var iconMap = {
    add: 'bi-plus-circle', archive: 'bi-archive', backup: 'bi-database-down', book: 'bi-journal-text', capability: 'bi-award',
    cart: 'bi-cart', chat: 'bi-chat-dots', check: 'bi-check-circle', choose: 'bi-ui-checks', classroom: 'bi-easel',
    conflicts: 'bi-diagram-3', connections: 'bi-plug', copy: 'bi-copy', delete: 'bi-trash', delete2: 'bi-trash',
    deleteall: 'bi-trash3', document: 'bi-file-earmark-text', edit: 'bi-pencil-square', education: 'bi-mortarboard',
    excel: 'bi-file-earmark-spreadsheet', fill: 'bi-input-cursor-text', filter: 'bi-funnel', hand: 'bi-hand-index-thumb',
    help: 'bi-question-circle', history: 'bi-clock-history', import: 'bi-box-arrow-in-down', list: 'bi-list-ul',
    lock: 'bi-lock', logout: 'bi-box-arrow-right', medal: 'bi-award', menu: 'bi-list', off: 'bi-toggle-off',
    on: 'bi-toggle-on', paths: 'bi-signpost-split', pdf: 'bi-file-earmark-pdf', preview: 'bi-eye',
    print: 'bi-printer', printall: 'bi-printer-fill', qualification: 'bi-patch-check', reset: 'bi-arrow-counterclockwise',
    results: 'bi-clipboard-data', save: 'bi-floppy', select: 'bi-check2-square', select2: 'bi-list-check',
    settings: 'bi-gear', stats: 'bi-bar-chart', teacher: 'bi-people', thumb_up: 'bi-hand-thumbs-up',
    timetable: 'bi-calendar-week', tracking: 'bi-graph-up-arrow', tree: 'bi-diagram-2', tree2: 'bi-bezier2',
    unlock: 'bi-unlock', user: 'bi-person-circle', wheel: 'bi-sliders'
  };

  function api(path, options) {
    options = options || {};
    options.credentials = 'same-origin';
    options.headers = options.headers || {};
    options.headers['X-Requested-With'] = 'XMLHttpRequest';
    return fetch(backendBase + path, options).then(function (response) {
      var json = (response.headers.get('content-type') || '').indexOf('application/json') >= 0;
      return (json ? response.json() : response.text()).then(function (body) {
        if (!response.ok) throw new Error(body && body.message ? body.message : 'Error HTTP ' + response.status);
        return body;
      });
    });
  }

  function currentRoute() {
    var raw = window.location.hash.replace(/^#\/?/, '') || 'index';
    var parts = raw.split('?');
    return { page: (parts[0] || 'index').replace(/\.php$/, ''), query: parts[1] || '' };
  }

  function frontendUrl(url) {
    var parts = url.split('?');
    return 'index.html#/' + parts[0].replace(/^.*\//, '').replace(/\.php$/, '') + (parts[1] ? '?' + parts[1] : '');
  }

  function isBackendDocument(url) {
    var file = url.split('?')[0].replace(/^.*\//, '');
    return /^(?:pdf_|excel\.php|listado_|estadisticas\.php|.*_vista_previa(?:_[a-z_]+)?\.php)/.test(file);
  }

  function prepareFragment(html) {
    var template = document.createElement('template');
    template.innerHTML = String(html == null ? '' : html);
    Array.prototype.slice.call(template.content.querySelectorAll('img[src^="img/"]')).forEach(function (image) {
      var name = image.getAttribute('src').split('/').pop().replace(/\.[^.]+$/, '');
      if (!iconMap[name]) return;
      var icon = document.createElement('i');
      icon.className = 'bi ' + iconMap[name] + ' me-1';
      icon.setAttribute('aria-hidden', 'true');
      image.replaceWith(icon);
    });
    return template.innerHTML;
  }

  window.GestionIES = Object.assign(window.GestionIES || {}, {
    backendUrl: function (url) { return /^(?:[a-z]+:)?\/\//i.test(url) ? url : backendBase + url; },
    frontendUrl: frontendUrl,
    isBackendDocument: isBackendDocument,
    prepareFragment: prepareFragment,
    navigate: function (url) {
      var target = frontendUrl(url);
      if (window.location.href.slice(-target.length) === target) window.location.reload();
      else window.location.href = target;
    },
    reloadPage: function () { window.location.reload(); },
    open: function (url) { window.open(isBackendDocument(url) ? this.backendUrl(url) : frontendUrl(url), '_blank', 'noopener'); }
  });

  Vue.createApp({
    data: function () {
      return {
        checkingSession: true,
        loggingIn: false,
        credentials: { login: '', password: '' },
        loginError: '',
        session: { authenticated: false, menus: [] },
        openGroups: {},
        pageLoading: false,
        pageError: '',
        pageHtml: '',
        pageTitle: 'Inicio',
        route: currentRoute(),
        pageScripts: []
      };
    },
    computed: {
      roleLabel: function () {
        return { admin: 'Administración', jefeDepartamento: 'Jefatura de departamento', profesor: 'Profesorado' }[this.session.role] || this.session.role || '';
      }
    },
    methods: {
      iconFor: function (name) { return iconMap[name] || 'bi-circle'; },
      toggleGroup: function (id) { this.openGroups[id] = !this.openGroups[id]; },
      isActive: function (link) { return link && link.replace(/\.php(?:\?.*)?$/, '') === this.route.page; },
      activateMenu: function (item) {
        var link = item.link || '';
        if (link === 'logout.php') return this.logout();
        if (link.indexOf('javascript:') === 0) {
          try { Function(link.substring(11))(); } catch (error) { this.pageError = error.message; }
          return;
        }
        window.GestionIES.navigate(link);
      },
      login: function () {
        var self = this;
        self.loggingIn = true;
        self.loginError = '';
        var body = new URLSearchParams(self.credentials);
        api('login.php', { method: 'POST', body: body }).then(function () {
          window.location.hash = '/index';
          window.location.reload();
        }).catch(function (error) { self.loginError = error.message; }).finally(function () { self.loggingIn = false; });
      },
      logout: function () {
        api('logout.php', { method: 'POST' }).finally(function () {
          window.location.hash = '';
          window.location.reload();
        });
      },
      reloadPage: function () { window.GestionIES.reloadPage(); },
      loadSession: function () {
        var self = this;
        return api('api/session.php').then(function (session) {
          self.session = session;
          session.menus.forEach(function (item) {
            if (item.link && item.link.replace(/\.php(?:\?.*)?$/, '') === self.route.page) self.openGroups[item.id] = true;
          });
          self.checkingSession = false;
          if (session.authenticated) return self.loadPage();
        }).catch(function (error) {
          if (!/sesión|autentic/i.test(error.message)) self.loginError = error.message;
          self.session = { authenticated: false, menus: [] };
          self.checkingSession = false;
        });
      },
      loadPage: function () {
        var self = this;
        self.pageLoading = true;
        self.pageError = '';
        var query = 'page=' + encodeURIComponent(self.route.page) + (self.route.query ? '&' + self.route.query : '');
        return api('view.php?' + query).then(function (html) {
          var template = document.createElement('template');
          template.innerHTML = html;
          self.pageScripts = Array.prototype.slice.call(template.content.querySelectorAll('script')).map(function (script) {
            var descriptor = { src: script.getAttribute('src'), code: script.textContent };
            script.remove();
            return descriptor;
          });
          var heading = template.content.querySelector('h1');
          self.pageTitle = heading ? heading.textContent.trim() : 'Gestión IES';
          self.pageHtml = prepareFragment(template.innerHTML);
          return self.$nextTick().then(function () { return self.runScripts(); });
        }).catch(function (error) {
          if (/sesión|autentic/i.test(error.message)) {
            self.session.authenticated = false;
          } else self.pageError = error.message;
        }).finally(function () { self.pageLoading = false; });
      },
      runScripts: function () {
        return this.pageScripts.reduce(function (chain, descriptor) {
          return chain.then(function () {
            return new Promise(function (resolve, reject) {
              var script = document.createElement('script');
              if (descriptor.src) {
                if (/main\.js|tinymce\.min\.js|bootstrap|sweetalert/i.test(descriptor.src)) return resolve();
                script.src = descriptor.src.replace(/^\.\.\/frontend\//, '');
                script.onload = resolve;
                script.onerror = function () { reject(new Error('No se pudo cargar ' + descriptor.src)); };
              } else {
                script.textContent = descriptor.code;
              }
              document.body.appendChild(script);
              if (!descriptor.src) resolve();
            });
          });
        }, Promise.resolve());
      },
      handleContentClick: function (event) {
        var anchor = event.target.closest('a[href]');
        if (!anchor) return;
        var href = anchor.getAttribute('href');
        if (!href || href.charAt(0) === '#' || /^(?:mailto:|tel:|https?:\/\/)/i.test(href)) return;
        if (href.indexOf('javascript:') === 0) return;
        if (!/\.php(?:[?#]|$)/.test(href)) return;
        event.preventDefault();
        if (isBackendDocument(href)) window.open(backendBase + href, anchor.target || '_blank', 'noopener');
        else if (anchor.target === '_blank') window.open(frontendUrl(href), '_blank', 'noopener');
        else window.GestionIES.navigate(href);
      }
    },
    mounted: function () {
      var self = this;
      window.addEventListener('gestionies:unauthorized', function () { self.session.authenticated = false; });
      this.loadSession();
    }
  }).mount('#app');
})(window, document, Vue);
