/**
 * Dinamički generiše i prikazuje navigacioni meni na sajtu.
 */
export function initNavigation() {
    let links = [                   //Linkovi i tekst za navigaciju

        {
            slug: "index.php",
            text: "Početna"
        },
        {
            slug: "usluge.php",
            text: "Usluge"
        },
        {
            slug: "o_autoru.php",
            text: "O autoru"
        }
    ];

    function linkMaker(link) {
        let line = `<li class="nav-item nav_listItem"><a href="${link.slug}" class="nav-item nav-link nav_listItemLink">${link.text}</a></li>`;
        return line;
    }

    let navLink = "";

    links.forEach(function (link) {
        navLink += linkMaker(link);
    })

    let navHolder = document.querySelector(".navigation");

    if (navHolder) {
        navHolder.innerHTML = navLink;
    }

    // Ikonice za društvene mreže
    const nav_icons = [
    {
        name: "facebook",
        slug: "#",
        faClass: "fab fa-facebook-f"
    },
    {
        name: "instagram",
        slug: "#",
        faClass: "fab fa-instagram"
    },
    {
        name: "x",
        slug: "#",
        faClass: "fab fa-x-twitter"
    },
]

    function socialIconMaker(icon) {
        // Font Awesome klase za svaku mrežu
        const faClasses = {
            facebook: "fab fa-facebook-f",
            instagram: "fab fa-instagram",
            x: "fab fa-x-twitter"
        };
        let line = `<li class="nav_socialIconsHolderItem">
            <a class="nav_socialIconsHolderItemLink nav_socialIconsHolderItemLink--${icon.name} d-flex align-items-center" href="${icon.slug}">
                <i class="${faClasses[icon.name]}" aria-hidden="true"></i>
            </a>
        </li>`;
        return line;
    }

    let nav_iconAllCode = "";

    nav_icons.forEach(function (icon) {
        nav_iconAllCode += socialIconMaker(icon);
    });

    let nav_iconContainer = document.querySelector(".nav_socialIconsHolder");

    if (nav_iconContainer) {
        nav_iconContainer.innerHTML = nav_iconAllCode;
    }

    //Navigacija - meni koji ostaje na vrhu
    const navbar = document.querySelector('.navHolder');

    function updateNavbarOpacity() {
        if (window.scrollY <= 400) {
            navbar.style.opacity = '1';
        } else {
            navbar.style.opacity = '0.7';
        }
    }

    function scrollNavbar() {
        if (window.scrollY > 400) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        updateNavbarOpacity();
    }

    // Mis van navigacije
    navbar.addEventListener('mouseenter', () => {
        if (window.scrollY > 400) {
            navbar.style.opacity = '1';
        }
    });

    // Mis unutar navigacije
    navbar.addEventListener('mouseleave', () => {
        if (window.scrollY > 400) {
            navbar.style.opacity = '0.7';
        }
    });

    window.addEventListener('scroll', () => {
        if (window.innerWidth > 990) {
            scrollNavbar();
        }
    });

}