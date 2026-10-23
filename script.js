
(function () {
  'use strict';

  const STORAGE_KEYS = {
    THEME: 'mon-journal-theme',
    COMMENTS_PREFIX: 'mon-journal-comments-'
  };

  /* ==========================================================================
     1. GESTION DU MODE CLAIR / SOMBRE 
     ========================================================================== */
  function initTheme() {
    const savedTheme = localStorage.getItem(STORAGE_KEYS.THEME);
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const currentTheme = savedTheme || (prefersDark ? 'dark' : 'light');

    applyTheme(currentTheme);
    const toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn) {
      
      toggleBtn.onclick = function () {
        const activeTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
        localStorage.setItem(STORAGE_KEYS.THEME, newTheme);
        showToast(newTheme === 'dark' ? '🌙 Mode nuit activé' : '☀️ Mode doux activé');
      };
    }
  }

  function applyTheme(theme) {
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    } else {
      document.documentElement.removeAttribute('data-theme');
    }

    const toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn) {
      const icon = toggleBtn.querySelector('.theme-icon');
      if (icon) {
        icon.textContent = theme === 'dark' ? '☀️' : '🌙';
      }
      toggleBtn.setAttribute('title', theme === 'dark' ? 'Passer au mode doux' : 'Passer au mode nuit');
      toggleBtn.setAttribute('aria-label', theme === 'dark' ? 'Passer au mode doux' : 'Passer au mode nuit');
    }
  }

  /* ==========================================================================
     2. RECHERCHE ET FILTRAGE D'ARTICLES 
     ========================================================================== */
  function initSearchAndFilter() {
    const searchInput = document.getElementById('article-search');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.articles-grid .card');
    const countElement = document.querySelector('.section-count');
    const emptyState = document.getElementById('search-empty-state');

    if (!searchInput && filterButtons.length === 0) return;

    let currentCategory = 'all';
    let currentSearchTerm = '';

    function filterArticles() {
      let visibleCount = 0;

      cards.forEach(card => {
        const title = (card.querySelector('.card-title')?.textContent || '').toLowerCase();
        const excerpt = (card.querySelector('.card-excerpt')?.textContent || '').toLowerCase();
        const badge = (card.querySelector('.card-badge')?.textContent || '').toLowerCase();
        const cardCategory = card.getAttribute('data-category') || '';

        const matchesCategory = (currentCategory === 'all') || (cardCategory === currentCategory);

        const matchesSearch = !currentSearchTerm || 
                              title.includes(currentSearchTerm) || 
                              excerpt.includes(currentSearchTerm) || 
                              badge.includes(currentSearchTerm);

        if (matchesCategory && matchesSearch) {
          card.classList.remove('card-hidden');
          visibleCount++;
        } else {
          card.classList.add('card-hidden');
        }
      });

      if (countElement) {
        if (visibleCount === 0) {
          countElement.textContent = 'Aucun résultat';
        } else if (visibleCount === 1) {
          countElement.textContent = '1 page trouvée';
        } else {
          countElement.textContent = `${visibleCount} pages trouvées`;
        }
      }
      if (emptyState) {
        if (visibleCount === 0) {
          emptyState.classList.remove('empty-hidden');
        } else {
          emptyState.classList.add('empty-hidden');
        }
      }
    }
    if (searchInput) {
      searchInput.oninput = function (e) {
        currentSearchTerm = e.target.value.trim().toLowerCase();
        filterArticles();
      };

      const clearBtn = document.getElementById('search-clear-btn');
      if (clearBtn) {
        clearBtn.onclick = function () {
          searchInput.value = '';
          currentSearchTerm = '';
          filterArticles();
          searchInput.focus();
        };
      }
    }

    filterButtons.forEach(btn => {
      btn.onclick = function () {
        filterButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentCategory = btn.getAttribute('data-filter') || 'all';
        filterArticles();
      };
    });
  }

  /* ==========================================================================
     3. LECTURE PROGRESSIVE AVEC INDICATEUR DE SCROLL 
     ========================================================================== */
  function initScrollProgress() {
    let progressBar = document.getElementById('reading-progress');
    
    if (!progressBar) {
      progressBar = document.createElement('div');
      progressBar.id = 'reading-progress';
      progressBar.className = 'reading-progress-bar';
      progressBar.setAttribute('aria-hidden', 'true');
      document.body.prepend(progressBar);
    }

    function updateProgress() {
      const isArticlePage = document.querySelector('.article-page') !== null;
      if (!isArticlePage) {
        progressBar.style.width = '0%';
        return;
      }

      const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
      if (totalHeight > 0) {
        const progress = (window.scrollY / totalHeight) * 100;
        progressBar.style.width = `${Math.min(100, Math.max(0, progress))}%`;
      } else {
        progressBar.style.width = '0%';
      }
    }

    window.removeEventListener('scroll', updateProgress);
    window.addEventListener('scroll', updateProgress, { passive: true });
    updateProgress();
  }

  /* ==========================================================================
     4. BOUTON RETOUR EN HAUT 
     ========================================================================== */
  function initBackToTop() {
    let backToTopBtn = document.getElementById('back-to-top');

    if (!backToTopBtn) {
      backToTopBtn = document.createElement('button');
      backToTopBtn.id = 'back-to-top';
      backToTopBtn.className = 'back-to-top-btn';
      backToTopBtn.setAttribute('aria-label', 'Remonter en haut de la page');
      backToTopBtn.setAttribute('title', 'Remonter tout en haut ♡');
      backToTopBtn.innerHTML = '<span class="arrow-up" aria-hidden="true">↑</span><span class="heart-icon" aria-hidden="true">♡</span>';
      document.body.appendChild(backToTopBtn);
    }

    function toggleVisibility() {
      if (window.scrollY > 280) {
        backToTopBtn.classList.add('visible');
      } else {
        backToTopBtn.classList.remove('visible');
      }
    }

    backToTopBtn.onclick = function () {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    };

    window.removeEventListener('scroll', toggleVisibility);
    window.addEventListener('scroll', toggleVisibility, { passive: true });
    toggleVisibility();
  }

  /* ==========================================================================
     5. PARTAGE D'ARTICLES SUR RÉSEAUX SOCIAUX 
     ========================================================================== */
  function initShareButtons() {
    const shareContainer = document.querySelector('.share-section');
    if (!shareContainer) return;

    const copyBtn = shareContainer.querySelector('.share-copy');
    const twitterLink = shareContainer.querySelector('.share-twitter');
    const whatsappLink = shareContainer.querySelector('.share-whatsapp');
    const pinterestLink = shareContainer.querySelector('.share-pinterest');
    const nativeBtn = shareContainer.querySelector('.share-native');

    const pageUrl = window.location.href;
    const pageTitle = document.title;

    if (copyBtn) {
      copyBtn.onclick = function (e) {
        e.preventDefault();
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(pageUrl).then(() => {
            showToast('Lien de la page copié dans le presse-papier ! ✨');
          }).catch(() => {
            fallbackCopy(pageUrl);
          });
        } else {
          fallbackCopy(pageUrl);
        }
      };
    }

    function fallbackCopy(text) {
      const input = document.createElement('input');
      input.value = text;
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      document.body.removeChild(input);
      showToast('Lien copié dans le presse-papier ! ✨');
    }

    // Partage Twitter
    if (twitterLink) {
      const tweetText = encodeURIComponent(`Découvre cette douce page de journal : "${pageTitle}"`);
      twitterLink.href = `https://twitter.com/intent/tweet?text=${tweetText}&url=${encodeURIComponent(pageUrl)}`;
      twitterLink.onclick = function (e) {
        e.preventDefault();
        window.open(this.href, '_blank', 'width=600,height=400');
      };
    }

    // Partage WhatsApp
    if (whatsappLink) {
      whatsappLink.href = `https://api.whatsapp.com/send?text=${encodeURIComponent(pageTitle + ' - ' + pageUrl)}`;
      whatsappLink.onclick = function (e) {
        e.preventDefault();
        window.open(this.href, '_blank', 'width=600,height=450');
      };
    }

    // Partage natif mobile
    if (nativeBtn) {
      if (navigator.share) {
        nativeBtn.style.display = 'inline-flex';
        nativeBtn.onclick = function () {
          navigator.share({
            title: pageTitle,
            text: 'Une douce lecture sur Mon Journal Doux',
            url: pageUrl
          }).catch(() => {});
        };
      } else {
        nativeBtn.style.display = 'none';
      }
    }
  }

  /* ==========================================================================
     6. SYSTÈME DE COMMENTAIRES INTERACTIF 
     ========================================================================== */
  function getArticleId() {
    const path = window.location.pathname;
    if (path.includes('article-1')) return 'article-1';
    if (path.includes('article-2')) return 'article-2';
    if (path.includes('article-3')) return 'article-3';
    return null;
  }

  function getDefaultComments(articleId) {
    const defaults = {
      'article-1': [
        {
          id: 'c1',
          author: 'Léa',
          avatar: '🌸',
          date: 'Il y a 3 heures',
          message: 'Quel régal de lire ces mots ce matin avec mon café au lait d\'avoine ! Ce rituel sans écran a complètement transformé mes journées.',
          likes: 4
        },
        {
          id: 'c2',
          author: 'Chloé',
          avatar: '✨',
          date: 'Hier à 19:42',
          message: 'J\'adore la citation de Saint-Exupéry. Prendre le temps d\'écrire le matin m\'aide tellement à apaiser mes pensées avant d\'aller travailler.',
          likes: 2
        }
      ],
      'article-2': [
        {
          id: 'c3',
          author: 'Camille',
          avatar: '🎀',
          date: 'Il y a 1 jour',
          message: 'Les carnets avec washi tapes et stickers, c\'est toute mon enfance et encore ma passion d\'adulte ! Merci pour ces doux exercices de journaling.',
          likes: 5
        },
        {
          id: 'c4',
          author: 'Sarah',
          avatar: '🐱',
          date: 'Il y a 3 jours',
          message: 'Le "Brain Dump" de 10 minutes a sauvé mon sommeil plus d\'une fois. Un article si joliment écrit !',
          likes: 3
        }
      ],
      'article-3': [
        {
          id: 'c5',
          author: 'Juliette',
          avatar: '🕯️',
          date: 'Il y a 5 heures',
          message: 'L\'odeur de la bougie à la rose et le mode avion à 21h30... je teste ça dès ce soir ! Merci pour cette bulle de bienveillance.',
          likes: 6
        },
        {
          id: 'c6',
          author: 'Manon',
          avatar: '☕',
          date: 'Hier à 22:15',
          message: 'Un véritable doudou cet article. La palette rose et le texte justifié rendent la lecture tellement apaisante.',
          likes: 3
        }
      ]
    };
    return defaults[articleId] || [];
  }

  function initComments() {
    const articleId = getArticleId();
    if (!articleId) return;

    const commentsSection = document.getElementById('comments-section');
    if (!commentsSection) return;

    const storageKey = STORAGE_KEYS.COMMENTS_PREFIX + articleId;
    let comments = [];

    try {
      const saved = localStorage.getItem(storageKey);
      if (saved) {
        comments = JSON.parse(saved);
      } else {
        comments = getDefaultComments(articleId);
        localStorage.setItem(storageKey, JSON.stringify(comments));
      }
    } catch (e) {
      comments = getDefaultComments(articleId);
    }

    renderComments(comments, storageKey);

    // Formulaire d'ajout
    const form = document.getElementById('comment-form');
    if (form) {
      form.onsubmit = function (e) {
        e.preventDefault();

        const authorInput = document.getElementById('comment-author');
        const messageInput = document.getElementById('comment-message');
        const selectedAvatar = document.querySelector('input[name="comment-avatar"]:checked')?.value || '🌸';

        const author = authorInput?.value.trim();
        const message = messageInput?.value.trim();

        if (!author || !message) {
          showToast('Veuillez remplir votre nom et votre mot doux ♡');
          return;
        }

        const newComment = {
          id: 'user-' + Date.now(),
          author: author,
          avatar: selectedAvatar,
          date: 'À l\'instant',
          message: message,
          likes: 0,
          isUser: true
        };

        comments.unshift(newComment);
        try {
          localStorage.setItem(storageKey, JSON.stringify(comments));
        } catch (e) {}

        renderComments(comments, storageKey);
        form.reset();
        showToast('Votre mot doux a été publié avec succès ! 💌');
      };
    }
  }

  function renderComments(comments, storageKey) {
    const list = document.getElementById('comments-list');
    const countBadge = document.getElementById('comments-count-badge');
    if (!list) return;

    if (countBadge) {
      countBadge.textContent = `${comments.length} mot${comments.length > 1 ? 's' : ''} doux`;
    }

    if (comments.length === 0) {
      list.innerHTML = '<p class="no-comments">Soyez la première à déposer une douce pensée ici... ♡</p>';
      return;
    }

    list.innerHTML = comments.map(c => `
      <article class="comment-item ${c.isUser ? 'user-comment' : ''}" data-id="${c.id}">
        <div class="comment-avatar-bubble" aria-hidden="true">${c.avatar}</div>
        <div class="comment-content">
          <div class="comment-header">
            <span class="comment-author">${escapeHtml(c.author)}</span>
            <time class="comment-date">${c.date}</time>
          </div>
          <p class="comment-body">${escapeHtml(c.message)}</p>
          <div class="comment-actions">
            <button type="button" class="comment-like-btn" data-id="${c.id}" aria-label="Aimer ce commentaire">
              <span class="like-heart" aria-hidden="true">♡</span>
              <span class="like-count">${c.likes || 0}</span>
            </button>
            ${c.isUser ? `<button type="button" class="comment-delete-btn" data-id="${c.id}" aria-label="Supprimer mon mot doux">Supprimer</button>` : ''}
          </div>
        </div>
      </article>
    `).join('');

    //  likes
    list.querySelectorAll('.comment-like-btn').forEach(btn => {
      btn.onclick = function () {
        const id = btn.getAttribute('data-id');
        const comment = comments.find(c => c.id === id);
        if (comment) {
          const isLiked = btn.classList.toggle('liked');
          comment.likes = (comment.likes || 0) + (isLiked ? 1 : -1);
          btn.querySelector('.like-heart').textContent = isLiked ? '❤️' : '♡';
          btn.querySelector('.like-count').textContent = comment.likes;
          try {
            localStorage.setItem(storageKey, JSON.stringify(comments));
          } catch (e) {}
        }
      };
    });

    //  la suppression
    list.querySelectorAll('.comment-delete-btn').forEach(btn => {
      btn.onclick = function () {
        const id = btn.getAttribute('data-id');
        comments = comments.filter(c => c.id !== id);
        try {
          localStorage.setItem(storageKey, JSON.stringify(comments));
        } catch (e) {}
        renderComments(comments, storageKey);
        showToast('Votre mot doux a été retiré.');
      };
    });
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  /* ==========================================================================
     NOTIFICATIONS
     ========================================================================== */
  function showToast(message) {
    let toast = document.getElementById('journal-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'journal-toast';
      toast.className = 'journal-toast';
      document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.classList.add('toast-show');

    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => {
      toast.classList.remove('toast-show');
    }, 3200);
  }

  /* ==========================================================================
     8. NAVIGATION FLUIDE ENTRE ARTICLES (SPA LÉGÈRE ET ROBUSTE)
     ========================================================================== */
  function normalizeUrl(rawHref) {
    try {
      const url = new URL(rawHref, window.location.href);

      // Correction automatique : si une URL pointe par erreur vers /pages/index.html
      // on redirige proprement vers /index.html à la racine du projet
      if (url.pathname.endsWith('/pages/index.html')) {
        url.pathname = url.pathname.replace(/\/pages\/index\.html$/, '/index.html');
      }

      return url;
    } catch (e) {
      return null;
    }
  }

  function initSmoothNavigation() {
    document.addEventListener('click', function (e) {
      const link = e.target.closest('a');
      if (!link) return;

      const rawHref = link.getAttribute('href');
      if (!rawHref) return;

      // Ignorer les protocoles externes, mailto, tel, ou nouvel onglet
      if (rawHref.startsWith('mailto:') || rawHref.startsWith('tel:') || link.target === '_blank') {
        return;
      }

      // Si le lien est une ancre locale (#section) sur la page actuelle
      if (rawHref.startsWith('#')) {
        const targetEl = document.querySelector(rawHref);
        if (targetEl) {
          e.preventDefault();
          targetEl.scrollIntoView({ behavior: 'smooth' });
          history.pushState(null, '', rawHref);
        }
        return;
      }

      const targetUrl = normalizeUrl(rawHref);
      if (!targetUrl) return;

      // Vérifier que c'est bien une navigation sur le même domaine
      if (targetUrl.origin !== window.location.origin) {
        return;
      }

      // Vérifier si le lien pointe vers une page HTML du blog
      const isHtmlPage = targetUrl.pathname.endsWith('.html') || targetUrl.pathname.endsWith('/');
      if (!isHtmlPage) return;

      e.preventDefault();
      loadPageViaSPA(targetUrl.href);
    });

    // Prise en charge des boutons Précédent / Suivant du navigateur
    window.addEventListener('popstate', function () {
      const normalizedCurrent = normalizeUrl(window.location.href);
      if (normalizedCurrent) {
        loadPageViaSPA(normalizedCurrent.href, false);
      }
    });
  }

  function loadPageViaSPA(targetHref, pushToHistory = true) {
    const targetUrl = normalizeUrl(targetHref);
    if (!targetUrl) {
      window.location.href = targetHref;
      return;
    }

    const main = document.querySelector('main');
    if (!main) {
      window.location.href = targetUrl.href;
      return;
    }

    // URL sans le hash (#) pour la requête fetch
    const fetchUrl = targetUrl.origin + targetUrl.pathname + targetUrl.search;

    main.classList.add('page-fade-out');

    fetch(fetchUrl)
      .then(res => {
        if (!res.ok) {
          throw new Error('Page introuvable : statut ' + res.status);
        }
        return res.text();
      })
      .then(htmlText => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlText, 'text/html');

        const newMain = doc.querySelector('main');
        const newHeader = doc.querySelector('.site-header');
        const newFooter = doc.querySelector('.site-footer');
        const newTitle = doc.querySelector('title')?.textContent || document.title;

        if (!newMain) {
          window.location.href = targetUrl.href;
          return;
        }

        setTimeout(() => {
          // Mise à jour de l'en-tête (pour garantir la validité des liens relatifs ../ vs .)
          const currentHeader = document.querySelector('.site-header');
          if (currentHeader && newHeader) {
            currentHeader.innerHTML = newHeader.innerHTML;
          }

          // Mise à jour du contenu principal
          main.innerHTML = newMain.innerHTML;

          // Mise à jour du pied de page
          const currentFooter = document.querySelector('.site-footer');
          if (currentFooter && newFooter) {
            currentFooter.innerHTML = newFooter.innerHTML;
          }

          document.title = newTitle;

          // Mise à jour de l'URL dans la barre d'adresse
          if (pushToHistory) {
            history.pushState(null, newTitle, targetUrl.href);
          }

          // Gestion du défilement : vers l'ancre demandée (#articles, etc.) ou tout en haut
          if (targetUrl.hash) {
            setTimeout(() => {
              const hashTarget = document.querySelector(targetUrl.hash);
              if (hashTarget) {
                hashTarget.scrollIntoView({ behavior: 'smooth' });
              } else {
                window.scrollTo({ top: 0, behavior: 'instant' });
              }
            }, 60);
          } else {
            window.scrollTo({ top: 0, behavior: 'instant' });
          }

          main.classList.remove('page-fade-out');
          main.classList.add('page-fade-in');
          setTimeout(() => main.classList.remove('page-fade-in'), 300);

          // Ré-attachement de tous les modules JS (thème, recherche, commentaires, etc.)
          initTheme();
          reinitPageModules();
        }, 150);
      })
      .catch(err => {
        console.warn('Navigation SPA : repli vers navigation normale.', err);
        // En cas de repli, naviguer vers l'URL corrigée
        window.location.href = targetUrl.href;
      });
  }

  function reinitPageModules() {
    initSearchAndFilter();
    initScrollProgress();
    initBackToTop();
    initShareButtons();
    initComments();
  }

  /* ==========================================================================
     INITIALISATION GLOBALE 
     ========================================================================== */
  function initAll() {
    initTheme();
    initSearchAndFilter();
    initScrollProgress();
    initBackToTop();
    initShareButtons();
    initComments();
    initSmoothNavigation();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

})();
