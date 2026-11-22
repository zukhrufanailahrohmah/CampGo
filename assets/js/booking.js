// CAMPGO - form reservasi: tombol cek stok (memanggil customer/booking/cek_stok.php)
document.addEventListener("DOMContentLoaded", function () {
    var form = document.getElementById("form-booking");
    if (!form) return;

    var metode = document.getElementById("pickup_method");
    var rowAlamat = document.getElementById("row-alamat");
    function toggleAlamat() {
        rowAlamat.style.display = metode.value === "doorstep_delivery" ? "" : "none";
    }
    metode.addEventListener("change", toggleAlamat);
    toggleAlamat();

    var hasil = document.getElementById("hasil-stok");
    document.getElementById("btn-cek-stok").addEventListener("click", function () {
        var data = new FormData(form);
        var q = new URLSearchParams();
        data.forEach(function (v, k) {
            if (/^(items|packages)\[/.test(k) && (v === "" || v === "0")) return;
            if (/^(start_date|end_date|pickup_method|items|packages)/.test(k)) q.append(k, v);
        });
        hasil.textContent = "Memeriksa...";
        fetch(window.CAMPGO_CEK_URL + "?" + q.toString())
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) { hasil.innerHTML = '<div class="flash flash-error"></div>'; hasil.firstChild.textContent = d.message; return; }
                var html = '<div class="flash ' + (d.ok ? "flash-success" : "flash-error") + '"></div>';
                hasil.innerHTML = html;
                var box = hasil.firstChild;
                var teks = (d.ok ? "Semua barang tersedia. " : "Ada barang yang tidak cukup. ") +
                    d.hari + " hari, estimasi total Rp " + d.total.toLocaleString("id-ID") + ".";
                d.barang.forEach(function (b) {
                    teks += "\n- " + b.nama + ": tersedia " + b.tersedia + ", dibutuhkan " + b.dibutuhkan + (b.cukup ? "" : " (KURANG)");
                });
                box.style.whiteSpace = "pre-line";
                box.textContent = teks;
            })
            .catch(function () { hasil.textContent = "Gagal memeriksa ketersediaan."; });
    });
});
