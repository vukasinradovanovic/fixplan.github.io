/**
 * Inicijalizuje hero sekciju sajta tako što dinamički generiše sadržaj za carousel na početnoj strani.
 */
export function initHero() {
    let carosel = [
        {
            activeStatus: true,
            img: "public/img/pexels-bidvine-517980-1249611.jpg",
            alt: "Slika 1",
            h: "Brz i lak pristup profesionalnim uslugama!",
            text: "Zakazivanje majstora i usluga nikada nije bilo jednostavnije.",
            href: "usluge.php"
        },
        {
            activeStatus: false,
            img: "public/img/pexels-pixabay-159045 (1).jpg",
            alt: "Slika 2",
            h: "Popravke, održavanje i renoviranje na jednom mestu.",
            text: "Vaše rešenje za svaki zadatak u domu ili kancelariji.",
            href: "usluge.php"
        },
        {
            activeStatus: false,
            img: "public/img/jeriden-villegas-VLPUm5wP5Z0-unsplash.jpg", // Dodaj svoju sliku ovde
            alt: "Slika 3",
            h: "Pouzdani majstori i kvalitetna usluga",
            text: "Izaberite uslugu i prepustite posao proverenim stručnjacima.",
            href: "usluge.php"
        }
    ];

    function caroselMaker(info) {
        let line = `<div class="carousel-item ${info.activeStatus ? "active" : ""} hero_carouselInnerItem">
                    <div class="hero_carouselInnerItemImage" style="background-image: url('${info.img}');"></div>
                    <div class="carousel-caption hero_carouselInnerItemCaption w-75 z-2">
                        <h2 class="hero_carouselInnerItemCaptionHeader">${info.h}</h2>
                        <p class="hero_carouselInnerItemCaptionText">${info.text}</p>
                        <a href="${info.href}" class="btn secondaryButton hero_carouselInnerItemCaptionButton">Pogledaj više</a>
                    </div>
                </div>`
        return line;
    }

    let caroselAllCode = "";

    carosel.forEach(function (info) {
        caroselAllCode += caroselMaker(info);
    });

    let caroselHolder = document.querySelector(".hero_carouselInner");

    if (caroselHolder) {
        caroselHolder.innerHTML = caroselAllCode;
    }
}