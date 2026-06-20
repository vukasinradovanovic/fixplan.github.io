/**
 * Prikazuje loader animaciju pri učitavanju stranice i nakon kratkog vremena je sakriva,
 * a zatim prikazuje glavni sadržaj sajta koristeći jQuery animacije.
 * * @param {boolean} isLoaderActive - Ako je false, loader se preskače i sadržaj se prikazuje odmah.
 */
export function initMainLoader(isLoaderActive = true) {
    if (!isLoaderActive) {
        // Ako loader nije aktivan, odmah sakrij loader i prikaži sadržaj bez animacije
        $("#loader").hide();
        $("#mainContent").show();
        return; // Prekida izvršavanje funkcije
    }

    // Originalna logika ako je loader aktivan
    setTimeout(function () {
        $("#loader").fadeOut(500, function () {
            $("#mainContent").fadeIn(500);
        });
    }, 1500);
}