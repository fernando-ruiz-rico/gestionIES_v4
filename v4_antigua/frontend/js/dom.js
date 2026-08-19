(function (window, document) {
  'use strict';

  var BACKEND_BASE = '../backend/';

  function isAbsolute(url) {
    return /^(?:[a-z]+:)?\/\//i.test(url) || url.charAt(0) === '/';
  }

  function backendUrl(url) {
    if (!url || isAbsolute(url) || url.charAt(0) === '#') return url;
    if (url.indexOf(BACKEND_BASE) === 0) return url;
    if (/^(?:ajax|modales)\//.test(url) || /\.php(?:[?#]|$)/.test(url)) return BACKEND_BASE + url;
    return url;
  }

  function encode(data) {
    var params = new URLSearchParams();
    if (!data) return params;
    Object.keys(data).forEach(function (key) {
      var value = data[key];
      if (Array.isArray(value)) value.forEach(function (item) { params.append(key + '[]', item); });
      else if (value !== undefined && value !== null) params.append(key, value);
    });
    return params;
  }

  function parseResponse(response) {
    if (response.status === 401) {
      window.dispatchEvent(new CustomEvent('gestionies:unauthorized'));
    }
    var contentType = response.headers.get('content-type') || '';
    return (contentType.indexOf('application/json') >= 0 ? response.json() : response.text()).then(function (body) {
      if (!response.ok) {
        var message = body && body.message ? body.message : ('Error HTTP ' + response.status);
        throw new Error(message);
      }
      return body;
    });
  }

  function request(url, options) {
    options = options || {};
    options.credentials = 'same-origin';
    options.headers = options.headers || {};
    options.headers['X-Requested-With'] = 'XMLHttpRequest';
    return fetch(backendUrl(url), options).then(parseResponse);
  }

  function get(url, data, callback) {
    if (typeof data === 'function') { callback = data; data = null; }
    var query = encode(data).toString();
    var target = url + (query ? (url.indexOf('?') >= 0 ? '&' : '?') + query : '');
    var promise = request(target);
    if (callback) promise.then(callback);
    return promise;
  }

  function post(url, data, callback) {
    if (typeof data === 'function') { callback = data; data = null; }
    var body = data instanceof FormData ? data : encode(data);
    var promise = request(url, { method: 'POST', body: body });
    if (callback) promise.then(callback);
    return promise;
  }

  function ajax(options) {
    var body = options.data;
    if (!(body instanceof FormData) && body && String(options.type || options.method).toUpperCase() !== 'GET') body = encode(body);
    var promise = request(options.url, { method: String(options.type || options.method || 'GET').toUpperCase(), body: body });
    promise.done = function (callback) { promise.then(callback); return promise; };
    promise.fail = function (callback) { promise.catch(callback); return promise; };
    return promise;
  }

  function DomCollection(elements) {
    this.elements = elements || [];
    this.length = this.elements.length;
    for (var i = 0; i < this.length; i += 1) this[i] = this.elements[i];
  }

  DomCollection.prototype.each = function (callback) {
    this.elements.forEach(function (element, index) { callback.call(element, index, element); });
    return this;
  };
  DomCollection.prototype.val = function (value) {
    if (value === undefined) return this.elements[0] ? this.elements[0].value : undefined;
    return this.each(function () {
      var normalized = value == null ? '' : value;
      if (this.type === 'date' && /^\d{2}\/\d{2}\/\d{4}$/.test(normalized)) {
        var parts = normalized.split('/');
        normalized = parts[2] + '-' + parts[1] + '-' + parts[0];
      }
      this.value = normalized;
    });
  };
  DomCollection.prototype.prop = function (name, value) {
    if (value === undefined) return this.elements[0] ? this.elements[0][name] : undefined;
    return this.each(function () { this[name] = value; });
  };
  DomCollection.prototype.attr = function (name, value) {
    if (value === undefined) return this.elements[0] ? this.elements[0].getAttribute(name) : undefined;
    return this.each(function () { this.setAttribute(name, value); });
  };
  DomCollection.prototype.removeAttr = function (name) { return this.each(function () { this.removeAttribute(name); }); };
  DomCollection.prototype.html = function (value) {
    if (value === undefined) return this.elements[0] ? this.elements[0].innerHTML : undefined;
    return this.each(function () { this.innerHTML = value == null ? '' : value; });
  };
  DomCollection.prototype.text = function (value) {
    if (value === undefined) return this.elements[0] ? this.elements[0].textContent : undefined;
    return this.each(function () { this.textContent = value == null ? '' : value; });
  };
  DomCollection.prototype.empty = function () { return this.html(''); };
  DomCollection.prototype.show = function () { return this.each(function () { this.hidden = false; this.style.display = ''; }); };
  DomCollection.prototype.hide = function () { return this.each(function () { this.hidden = true; this.style.display = 'none'; }); };
  DomCollection.prototype.toggle = function () { return this.each(function () { var hidden = this.hidden || getComputedStyle(this).display === 'none'; this.hidden = !hidden; this.style.display = hidden ? '' : 'none'; }); };
  DomCollection.prototype.append = function (content) {
    return this.each(function () {
      var parent = this;
      if (content instanceof DomCollection) content.elements.forEach(function (node) { parent.appendChild(node); });
      else if (content instanceof Node) parent.appendChild(content);
      else parent.insertAdjacentHTML('beforeend', String(content));
    });
  };
  DomCollection.prototype.on = function (eventName, callback) { return this.each(function () { this.addEventListener(eventName, callback); }); };
  DomCollection.prototype.click = function (callback) { return callback ? this.on('click', callback) : this.each(function () { this.click(); }); };
  DomCollection.prototype.trigger = function (eventName) { return this.each(function () { this.dispatchEvent(new Event(eventName, { bubbles: true })); }); };
  DomCollection.prototype.modal = function (action) {
    return this.each(function () {
      var instance = bootstrap.Modal.getOrCreateInstance(this);
      if (action === 'hide') instance.hide(); else instance.show();
    });
  };
  DomCollection.prototype.load = function (url, data, callback) {
    if (typeof data === 'function') { callback = data; data = null; }
    var collection = this;
    var promise = data ? post(url, data) : get(url);
    promise.then(function (html) {
      if (window.GestionIES && window.GestionIES.prepareFragment) html = window.GestionIES.prepareFragment(html);
      collection.html(html);
      if (callback) callback.call(collection.elements[0], html);
    }).catch(function (error) {
      if (window.mostrarMensaje) window.mostrarMensaje(error.message, 0);
    });
    return this;
  };

  function sortableItems(element, selector) {
    return Array.prototype.slice.call(element.querySelectorAll(selector || ':scope > *'));
  }

  function initSortable(element, options) {
    options = options || {};
    element.classList.add('gestion-sortable');
    element.__gestionSortable = options;
    function refresh() { sortableItems(element, options.items).forEach(function (item) { item.draggable = true; }); }
    refresh();
    new MutationObserver(refresh).observe(element, { childList: true, subtree: false });
    element.addEventListener('dragstart', function (event) {
      var item = event.target.closest(options.items || ':scope > *');
      if (!item || !element.contains(item)) return;
      element.__gestionDragged = item;
      item.classList.add('gestion-dragging');
      event.dataTransfer.effectAllowed = 'move';
    });
    element.addEventListener('dragover', function (event) {
      var dragged = element.__gestionDragged;
      if (!dragged) return;
      event.preventDefault();
      var target = event.target.closest(options.items || ':scope > *');
      if (!target || target === dragged || target.parentNode !== element) return;
      var rect = target.getBoundingClientRect();
      element.insertBefore(dragged, event.clientY < rect.top + rect.height / 2 ? target : target.nextSibling);
    });
    element.addEventListener('dragend', function () {
      var dragged = element.__gestionDragged;
      if (dragged) dragged.classList.remove('gestion-dragging');
      element.__gestionDragged = null;
      if (typeof options.update === 'function') options.update.call(element);
    });
  }

  DomCollection.prototype.sortable = function (options) {
    if (options === 'toArray') return this.elements[0] ? sortableItems(this.elements[0], this.elements[0].__gestionSortable && this.elements[0].__gestionSortable.items).map(function (item) { return item.id; }) : [];
    return this.each(function () { initSortable(this, options); });
  };
  DomCollection.prototype.accordion = function (options) {
    if (options === 'refresh') return this;
    options = options || {};
    return this.each(function () {
      var root = this;
      var headers = Array.prototype.slice.call(root.querySelectorAll(options.header || '.curso'));
      headers.forEach(function (header) {
        var panel = header.nextElementSibling;
        if (!panel) return;
        panel.hidden = true;
        header.setAttribute('role', 'button');
        header.tabIndex = 0;
        header.addEventListener('click', function () { panel.hidden = !panel.hidden; });
      });
    });
  };
  DomCollection.prototype.datepicker = function () {
    return this.each(function () {
      if (this.tagName !== 'INPUT') return;
      var input = this;
      var initial = input.value;
      input.type = 'date';
      if (/^\d{2}\/\d{2}\/\d{4}$/.test(initial)) {
        var initialParts = initial.split('/');
        input.value = initialParts[2] + '-' + initialParts[1] + '-' + initialParts[0];
      }
      if (input.form && !input.__gestionDateSubmit) {
        input.__gestionDateSubmit = true;
        input.form.addEventListener('submit', function () {
          var iso = input.value;
          if (!/^\d{4}-\d{2}-\d{2}$/.test(iso)) return;
          var parts = iso.split('-');
          input.type = 'text';
          input.value = parts[2] + '/' + parts[1] + '/' + parts[0];
          setTimeout(function () { input.type = 'date'; input.value = iso; }, 0);
        });
      }
    });
  };

  function dom(selector) {
    if (selector instanceof DomCollection) return selector;
    if (selector === window || selector === document || selector instanceof Node) return new DomCollection([selector]);
    if (typeof selector !== 'string') return new DomCollection([]);
    var text = selector.trim();
    if (text.charAt(0) === '<') {
      var template = document.createElement('template');
      template.innerHTML = text;
      return new DomCollection(Array.prototype.slice.call(template.content.children));
    }
    return new DomCollection(Array.prototype.slice.call(document.querySelectorAll(selector)));
  }

  window.dom = dom;
  window.http = {
    get: get,
    post: post,
    ajax: ajax,
    each: function (collection, callback) {
      if (Array.isArray(collection) || (collection && typeof collection.length === 'number')) Array.prototype.forEach.call(collection, function (value, index) { callback(index, value); });
      else Object.keys(collection || {}).forEach(function (key) { callback(key, collection[key]); });
      return collection;
    }
  };
  window.GestionIES = window.GestionIES || {};
  window.GestionIES.backendUrl = backendUrl;
})(window, document);
