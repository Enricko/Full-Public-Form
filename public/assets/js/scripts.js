function loadComponent(component, callback) {
  const url = `components/${component}.html`;


  if (document.getElementById(`${component}`)) {
    if (callback && typeof callback === 'function') {
      callback();
    }
    return;
  }

  fetch(url)
    .then(response => {
      if (!response.ok) {
        throw new Error(`Failed to load component: ${response.status} ${response.statusText}`);
      }
      return response.text();
    })
    .then(data => {

      let container = document.getElementById(`${component}-container`);
      if (!container) {
        container = document.createElement('div');
        container.id = `${component}-container`;
        document.body.appendChild(container);
      }


      container.innerHTML = data;



      if (callback && typeof callback === 'function') {
        setTimeout(() => {
          callback();
        }, 100);
      }


      setTimeout(() => {
        executeScriptsWithScope(container);
      }, 150);

      console.log(`Component ${component} loaded successfully`);
    })
    .catch(error => {
      console.error("Error loading component:", error);
    });
}


function executeScriptsWithScope(container) {

  const scripts = container.querySelectorAll('script');


  scripts.forEach(oldScript => {

    const newScript = document.createElement('script');


    if (oldScript.src) {
      newScript.src = oldScript.src;
    } else {


      newScript.textContent = `
        (function() {
          ${oldScript.textContent}
        })();
      `;
    }


    oldScript.parentNode.replaceChild(newScript, oldScript);
  });
}


function executeScripts(html, targetElement) {
  const container = targetElement || document.body;
  const tempDiv = document.createElement('div');
  tempDiv.innerHTML = html;

  const scripts = tempDiv.querySelectorAll('script');
  scripts.forEach(oldScript => {
    const newScript = document.createElement('script');


    Array.from(oldScript.attributes).forEach(attr => {
      newScript.setAttribute(attr.name, attr.value);
    });


    newScript.textContent = oldScript.textContent;


    container.appendChild(newScript);
  });
}


function saveCurrentPageState(page, id) {

  localStorage.setItem('currentPage', page);
  if (id) {
    localStorage.setItem('currentId', id);
  } else {
    localStorage.removeItem('currentId');
  }


  const params = new URLSearchParams(window.location.search);
  params.set('page', page);
  if (id) {
    params.set('id', id);
  } else {
    params.delete('id');
  }


  const newUrl = window.location.pathname + '?' + params.toString();
  window.history.replaceState({ path: newUrl, page: page, id: id }, '', newUrl);
}


function updateActiveSidebarItem(currentPage) {

  document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.classList.remove('active');
  });


  const activeLink = document.querySelector(`.sidebar .nav-link[onclick*="loadPage('${currentPage}')"]`);
  if (activeLink) {
    activeLink.classList.add('active');
  }
}


function showLoginModal() {

  if (document.getElementById('loginModal')) {
    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    loginModal.show();
    return;
  }


  loadComponent('login-modal', function () {
    setTimeout(() => {
      const loginModalEl = document.getElementById('loginModal');
      if (loginModalEl) {
        const loginModal = new bootstrap.Modal(loginModalEl);
        loginModal.show();
      }
    }, 200);
  });
}


function showRegisterModal() {

  if (document.getElementById('registerModal')) {
    const registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
    registerModal.show();
    return;
  }


  loadComponent('register-modal', function () {
    setTimeout(() => {
      const registerModalEl = document.getElementById('registerModal');
      if (registerModalEl) {
        const registerModal = new bootstrap.Modal(registerModalEl);
        registerModal.show();
      }
    }, 200);
  });
}


function hideLoginModal() {
  const loginModalEl = document.getElementById('loginModal');
  if (loginModalEl) {
    const loginModal = bootstrap.Modal.getInstance(loginModalEl);
    if (loginModal) {
      loginModal.hide();
    }
  }
}


function hideRegisterModal() {
  const registerModalEl = document.getElementById('registerModal');
  if (registerModalEl) {
    const registerModal = bootstrap.Modal.getInstance(registerModalEl);
    if (registerModal) {
      registerModal.hide();
    }
  }
}


function switchToRegister() {
  hideLoginModal();
  setTimeout(() => {
    showRegisterModal();
  }, 300);
}


function switchToLogin() {
  hideRegisterModal();
  setTimeout(() => {
    showLoginModal();
  }, 300);
}

document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM loaded, initializing application...");


  const urlParams = new URLSearchParams(window.location.search);
  const pageParam = urlParams.get('page');
  const idParam = urlParams.get('id');


  if (pageParam) {
    loadPage(pageParam, idParam || null);
  }

  else if (localStorage.getItem('currentPage')) {
    const storedPage = localStorage.getItem('currentPage');
    const storedId = localStorage.getItem('currentId') || null;
    loadPage(storedPage, storedId);
  }

  else {
    loadPage("home");
  }


  const navbarSearchForm = document.getElementById('navbar-search-form');
  if (navbarSearchForm) {
    navbarSearchForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const searchInput = document.getElementById('navbar-search-input').value.trim();

      if (searchInput) {

        localStorage.setItem('lastSearchQuery', searchInput);


        loadPage('search');


        const newUrl = window.location.pathname + '?page=search&q=' + encodeURIComponent(searchInput);
        window.history.pushState({ path: newUrl, page: 'search' }, '', newUrl);


        setTimeout(function () {
          if (document.getElementById('search-input')) {
            document.getElementById('search-input').value = searchInput;


            if (typeof window.performSearch === 'function') {
              window.performSearch(searchInput);
            }
          }
        }, 300);
      }
    });
  }


  window.onpopstate = function (event) {
    if (event.state && event.state.page) {
      loadPage(event.state.page, event.state.id || null);
    } else {

      const urlParams = new URLSearchParams(window.location.search);
      const page = urlParams.get('page') || 'home';
      const id = urlParams.get('id') || null;
      loadPage(page, id);
    }
  };

  console.log("Application initialized successfully");
});


window.performSearch = function (query) {

  console.log("Default performSearch called with query:", query);
};


window.loadPage = loadPage;
window.loadComponent = loadComponent;
window.showLoginModal = showLoginModal;
window.showRegisterModal = showRegisterModal;
window.hideLoginModal = hideLoginModal;
window.hideRegisterModal = hideRegisterModal;
window.switchToRegister = switchToRegister;
window.switchToLogin = switchToLogin;
