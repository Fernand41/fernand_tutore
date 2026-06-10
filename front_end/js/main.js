AOS.init({
    duration: 680,
    once: true,
    offset: 55
});

// Fonction utilitaire pour ajouter des event listeners de manière sûre
function safeAddEventListener(selector, event, callback) {
    var el = typeof selector === 'string' ? document.getElementById(selector) : selector;
    if (el) {
        el.addEventListener(event, callback);
    }
}

// Ajoute un loader sur les formulaires de login et d'inscription
function initFormSubmissionLoader(formId, btnId, contentId, spinnerId) {
    var form = document.getElementById(formId);
    var btn = document.getElementById(btnId);
    var content = document.getElementById(contentId);
    var spinner = document.getElementById(spinnerId);
    if (!form || !btn || !content || !spinner) return;

    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) return;
        event.preventDefault();
        btn.disabled = true;
        content.style.display = 'none';
        spinner.style.display = 'inline-flex';
        setTimeout(function() {
            form.submit();
        }, 300);
    });
}

// Confirmation + animation avant la déconnexion
function initLogoutWithLoader() {
    document.querySelectorAll('a[href*="auth_logout.php"]').forEach(function(link) {
        link.addEventListener('click', function(event) {
            var confirmed = window.confirm('Voulez-vous vraiment vous déconnecter ?');
            if (!confirmed) {
                event.preventDefault();
                return;
            }
            if (link.dataset.loading === 'true') {
                event.preventDefault();
                return;
            }
            event.preventDefault();
            link.dataset.loading = 'true';
            link.classList.add('disabled');
            link.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Déconnexion...';
            setTimeout(function() {
                window.location.href = link.href;
            }, 300);
        });
    });
}

// Loader pour les boutons de navigation de connexion/profil
function initNavLinkLoader() {
    document.querySelectorAll('a.nav-link.nav-cta').forEach(function(link) {
        link.addEventListener('click', function(event) {
            if (link.dataset.loading === 'true') {
                event.preventDefault();
                return;
            }
            event.preventDefault();
            link.dataset.loading = 'true';
            link.classList.add('disabled');
            link.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Chargement...';
            setTimeout(function() {
                window.location.href = link.href;
            }, 300);
        });
    });
}

// Loader pour les liens textuels de formulaire (Créer un compte / Se connecter)
function initFormTextLinkLoader() {
    document.querySelectorAll('a.form-link-loader').forEach(function(link) {
        link.addEventListener('click', function(event) {
            if (link.dataset.loading === 'true') {
                event.preventDefault();
                return;
            }
            event.preventDefault();
            link.dataset.loading = 'true';
            link.classList.add('disabled');
            var label = link.textContent.trim() || 'Chargement';
            link.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + label + '...';
            setTimeout(function() {
                window.location.href = link.href;
            }, 300);
        });
    });
}

initFormSubmissionLoader('loginForm', 'loginBtn', 'loginBtnContent', 'loginSpinner');
initFormSubmissionLoader('registerForm', 'registerBtn', 'registerBtnContent', 'registerSpinner');
initLogoutWithLoader();
initNavLinkLoader();
initFormTextLinkLoader();

/* NAVBAR SCROLL & ACTIVE LINK  */
window.addEventListener('scroll', function() {
    var navEl = document.getElementById('nav');
    if (navEl) navEl.classList.toggle('scrolled', window.scrollY > 60);
    var bttEl = document.getElementById('btt');
    if (bttEl) bttEl.classList.toggle('show', window.scrollY > 300);
    document.querySelectorAll('section[id]').forEach(function(sec) {
        var top = sec.offsetTop - 110,
            bot = top + sec.offsetHeight;
        if (window.scrollY >= top && window.scrollY < bot) {
            document.querySelectorAll('.nav-link').forEach(function(l) {
                l.classList.remove('active');
            });
            var lnk = document.querySelector('.nav-link[href="#' + sec.id + '"]');
            if (lnk) lnk.classList.add('active');
        }
    });
});

/*  SMOOTH SCROLL + MOBILE NAV CLOSE  */
document.querySelectorAll('a[href^="#"]').forEach(function(a) {
    a.addEventListener('click', function(e) {
        var href = this.getAttribute('href');
        if (href === '#') return;
        var t = document.querySelector(href);
        if (t) {
            e.preventDefault();
            // Close Bootstrap mobile navbar if open
            var navCollapse = document.getElementById('navmenu');
            if (navCollapse && navCollapse.classList.contains('show')) {
                var bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                } else {
                    navCollapse.classList.remove('show');
                }
            }
            // Scroll after slight delay to let navbar close
            setTimeout(function() {
                window.scrollTo({
                    top: t.offsetTop - 78,
                    behavior: 'smooth'
                });
            }, 50);
        }
    });
});


var searchOv = document.getElementById('searchOv');

safeAddEventListener('navSearchBtn', 'click', function() {
    if (searchOv) {
        searchOv.classList.add('open');
        document.body.style.overflow = 'hidden';
        setTimeout(function() {
            var searchInput = document.getElementById('searchInput');
            if (searchInput) searchInput.focus();
        }, 220);
    }
});

safeAddEventListener('searchClose', 'click', closeSearch);

// Close when clicking backdrop
if (searchOv) {
    searchOv.addEventListener('click', function(e) {
        if (e.target === searchOv) closeSearch();
    });
}

function closeSearch() {
    searchOv.classList.remove('open');
    document.body.style.overflow = '';
}

// Category buttons inside search box
document.querySelectorAll('.sovcat').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.sovcat').forEach(function(b) {
            b.classList.remove('active');
        });
        this.classList.add('active');
        var f = this.getAttribute('data-cat');
        closeSearch();
        setTimeout(function() {
            filterMenu(f);
            var menu = document.getElementById('menu');
            if (menu) {
                menu.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }, 300);
    });
});

// Trending tags fill the search input
document.querySelectorAll('.sovtrend .ttag').forEach(function(t) {
    t.addEventListener('click', function() {
        var searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = this.textContent.trim();
            searchInput.focus();
        }
    });
});


$(document).ready(function() {
	$('.magnific_popup').magnificPopup({
	  disableOn: 700,
	  type: 'iframe',
	  mainClass: 'mfp-fade',
	  removalDelay: 160,
	  preloader: false,
	  fixedContentPos: false,
	  disableOn: 300
	});	
});


function filterMenu(cat) {
    // sync filter buttons
    document.querySelectorAll('.filtbtn').forEach(function(b) {
        b.classList.toggle('active', b.getAttribute('data-f') === cat);
    });
    // sync category cards
    document.querySelectorAll('.catcard').forEach(function(c) {
        c.classList.toggle('active', c.getAttribute('data-filter') === cat);
    });
    // show/hide menu cards
    document.querySelectorAll('.mwrap').forEach(function(w) {
        var c = w.getAttribute('data-c');
        if (cat === 'all' || c === cat) {
            w.classList.remove('gone');
            w.style.opacity = '0';
            w.style.transform = 'translateY(16px)';
            setTimeout(function() {
                w.style.transition = 'opacity .38s,transform .38s';
                w.style.opacity = '1';
                w.style.transform = 'translateY(0)';
            }, 60);
        } else {
            w.classList.add('gone');
        }
    });
}

// Filter buttons
document.querySelectorAll('.filtbtn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        filterMenu(this.getAttribute('data-f'));
    });
});

// Category section cards â†’ scroll + filter
document.querySelectorAll('.catcard').forEach(function(card) {
    card.addEventListener('click', function() {
        var f = this.getAttribute('data-filter');
        var menu = document.getElementById('menu');
        if (menu) {
            window.scrollTo({
                top: menu.offsetTop - 80,
                behavior: 'smooth'
            });
        }
        setTimeout(function() {
            filterMenu(f);
        }, 480);
    });
});


var menuPop = document.getElementById('menuPop');
var mpQty = 1;

    if (!menuPop) {
        // menuPop n'existe pas sur cette page, ajouter des stubs pour éviter les erreurs
        window.openMenuPop = function() {};
        window.closeMenuPop = function() {};
    }
function openMenuPop(card) {
    if (!menuPop) return;
    var img = card.getAttribute('data-img');
    var title = card.getAttribute('data-title');
    var cat = card.getAttribute('data-cat');
    var price = card.getAttribute('data-price');
    var old = card.getAttribute('data-old');
    var rating = parseFloat(card.getAttribute('data-rating'));
    var reviews = card.getAttribute('data-reviews');
    var cal = card.getAttribute('data-cal');
    var time = card.getAttribute('data-time');
    var desc = card.getAttribute('data-desc');
    var tags = card.getAttribute('data-tags') || '';

    document.getElementById('mpImg').setAttribute('src', img);
    document.getElementById('mpCat').textContent = cat;
    document.getElementById('mpTitle').textContent = title;

    var full = Math.round(rating),
        empty = 5 - full;
    document.getElementById('mpStars').innerHTML =
        '<i class="fas fa-star"></i>'.repeat(full) + 'â˜†'.repeat(empty) +
        ' <span style="color:#bbb;font-size:.78rem;">' + rating + ' (' + reviews + ' reviews)</span>';

    document.getElementById('mpDesc').textContent = desc;

    document.getElementById('mpPrice').innerHTML =
        price + (old ? '<small style="color:#ccc;text-decoration:line-through;margin-left:8px;font-size:1rem;">' + old + '</small>' : '');

    document.getElementById('mpMeta').innerHTML =
        '<div class="mpm"><div class="mpmv">' + cal + ' kcal</div><div class="mpml">Calories</div></div>' +
        '<div class="mpm"><div class="mpmv">' + time + ' min</div><div class="mpml">Prep Time</div></div>' +
        '<div class="mpm"><div class="mpmv">' + rating + '/5</div><div class="mpml">Rating</div></div>';

    document.getElementById('mpTags').innerHTML =
        tags.split(',').filter(Boolean).map(function(t) {
            return '<span class="mptag">' + t.trim() + '</span>';
        }).join('');

    mpQty = 1;
    document.getElementById('mpQnum').textContent = 1;
    document.getElementById('mpAddCart').innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
    document.getElementById('mpAddCart').style.background = '';

    menuPop.classList.add('open');
    document.body.style.overflow = 'hidden';
}

// Card click open popup
document.querySelectorAll('.mcard').forEach(function(card) {
    card.addEventListener('click', function() {
        openMenuPop(this);
    });
});

// + button  open popup (stop propagation to avoid double firing)
document.querySelectorAll('.madd').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        openMenuPop(this.closest('.mcard'));
    });
});

// Heart toggle (no popup)
document.querySelectorAll('.mhrt').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var ico = this.querySelector('i');
        ico.classList.toggle('far');
        ico.classList.toggle('fas');
        this.style.color = ico.classList.contains('fas') ? 'var(--primary)' : '#ccc';
    });
});

// Close popup
var mpCloseBtn = document.getElementById('mpClose');
if (mpCloseBtn) {
    mpCloseBtn.addEventListener('click', closeMenuPop);
}
if (menuPop) {
    menuPop.addEventListener('click', function(e) {
    if (e.target === this) closeMenuPop();
    });
}

function closeMenuPop() {
    if (menuPop) {
        menuPop.classList.remove('open');
    }
    document.body.style.overflow = '';
}

// Qty +/-
safeAddEventListener('mpPlus', 'click', function() {
    var quantityEl = document.getElementById('mpQnum');
    if (quantityEl) quantityEl.textContent = ++mpQty;
});
safeAddEventListener('mpMinus', 'click', function() {
    if (mpQty > 1) {
        var quantityEl = document.getElementById('mpQnum');
        if (quantityEl) quantityEl.textContent = --mpQty;
    }
});

// Add to cart button
safeAddEventListener('mpAddCart', 'click', function() {
    var cartCountEl = document.getElementById('cartCount');
    if (cartCountEl) {
        var cnt = parseInt(cartCountEl.textContent) + mpQty;
        cartCountEl.textContent = cnt;
    }
    this.innerHTML = '<i class="fas fa-check"></i> Added to Cart!';
    this.style.background = 'linear-gradient(135deg,var(--green),#1a4a35)';
    var self = this;
    setTimeout(function() {
        closeMenuPop();
        self.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
        self.style.background = '';
    }, 1000);
});


safeAddEventListener('resBtn', 'click', function() {
    var btn = this;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Booking...';
    btn.disabled = true;
    setTimeout(function() {
        btn.innerHTML = '<i class="fas fa-calendar-check"></i> Confirm Reservation';
        btn.disabled = false;
        var ok = document.getElementById('resOk');
        if (ok) {
            ok.style.display = 'block';
            ok.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }, 1500);
});


safeAddEventListener('ctcBtn', 'click', function() {
    var btn = this;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    btn.disabled = true;
    setTimeout(function() {
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
        btn.disabled = false;
        var ok = document.getElementById('ctcOk');
        if (ok) {
            ok.style.display = 'block';
            ok.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }, 1500);
});


var galPop = document.getElementById('galPop');
var galData = [];
var galIdx = 0;

document.querySelectorAll('.gitem').forEach(function(item) {
    galData.push({
        img: item.getAttribute('data-gimg'),
        title: item.getAttribute('data-gtitle'),
        desc: item.getAttribute('data-gdesc')
    });
    item.addEventListener('click', function() {
        openGal(parseInt(this.getAttribute('data-gi')));
    });
});

function openGal(i) {
    galIdx = i;
    var g = galData[i];
    var gpImg = document.getElementById('gpImg');
    var gpTitle = document.getElementById('gpTitle');
    var gpDesc = document.getElementById('gpDesc');
    if (gpImg) gpImg.setAttribute('src', g.img);
    if (gpTitle) gpTitle.textContent = g.title;
    if (gpDesc) gpDesc.innerHTML = g.desc;
    if (galPop) {
        galPop.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

safeAddEventListener('gpClose', 'click', closeGal);
if (galPop) {
    galPop.addEventListener('click', function(e) {
        if (e.target === this) closeGal();
    });
}

function closeGal() {
    if (galPop) galPop.classList.remove('open');
    document.body.style.overflow = '';
}

safeAddEventListener('gpPrev', 'click', function() {
    openGal((galIdx - 1 + galData.length) % galData.length);
});
safeAddEventListener('gpNext', 'click', function() {
    openGal((galIdx + 1) % galData.length);
});

/*  ESC key closes everything */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSearch();
        closeMenuPop();
        closeGal();
        if (typeof $.magnificPopup !== 'undefined') $.magnificPopup.close();
    }
});


new Swiper('.tesSwiper', {
    slidesPerView: 1,
    spaceBetween: 22,
    loop: true,
    autoplay: {
        delay: 4000,
        disableOnInteraction: false
    },
    pagination: {
        el: '.swiper-pagination',
        clickable: true
    },
    breakpoints: {
        640: {
            slidesPerView: 2
        },
        1024: {
            slidesPerView: 3
        }
    }
});


var cH = 8,
    cM = 45,
    cS = 30;
setInterval(function() {
    cS--;
    if (cS < 0) {
        cS = 59;
        cM--;
    }
    if (cM < 0) {
        cM = 59;
        cH--;
    }
    if (cH < 0) {
        cH = 8;
        cM = 45;
        cS = 30;
    }
    var cdH = document.getElementById('cdH');
    var cdM = document.getElementById('cdM');
    var cdS = document.getElementById('cdS');
    if (cdH) cdH.textContent = String(cH).padStart(2, '0');
    if (cdM) cdM.textContent = String(cM).padStart(2, '0');
    if (cdS) cdS.textContent = String(cS).padStart(2, '0');
}, 1000);

/* â”€â”€ NEWSLETTER â”€â”€ */
safeAddEventListener('nlBtn', 'click', function() {
    var emailEl = document.getElementById('nlEmail');
    var email = emailEl ? emailEl.value : '';
    if (email && email.includes('@')) {
        var btn = this;
        btn.textContent = 'âœ“ Subscribed!';
        btn.style.background = '#4ade80';
        btn.style.color = '#222';
        if (emailEl) emailEl.value = '';
        setTimeout(function() {
            btn.textContent = 'Subscribe';
            btn.style.background = '';
            btn.style.color = '';
        }, 3000);
    }
});

/*  NUMBER COUNTER ANIMATION*/
var numAnimated = false;
window.addEventListener('scroll', function() {
    var hero = document.getElementById('hero');
    if (!numAnimated && hero && window.scrollY > hero.offsetHeight - 300) {
        numAnimated = true;
        document.querySelectorAll('.snum').forEach(function(el) {
            var txt = el.textContent;
            var num = parseInt(txt);
            var suf = txt.replace(/[0-9]/g, '');
            if (isNaN(num)) return;
            var start = 0;
            var step = Math.ceil(num / 55);
            var iv = setInterval(function() {
                start += step;
                if (start >= num) {
                    start = num;
                    clearInterval(iv);
                }
                el.textContent = start + suf;
            }, 1400 / 55);
        });
    }
});