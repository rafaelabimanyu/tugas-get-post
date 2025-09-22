<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Produk AJAX</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
.badge-kategori { font-size: 0.9rem; }
.table-hover tbody tr:hover { background-color: #f8f9fa; cursor: pointer; }
.alert-fixed { position: fixed; top: 20px; right: 20px; z-index: 9999; }
</style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4 text-center">Manajemen Produk Kompleks</h2>

    <!-- Alert -->
    <div id="alertBox" class="alert alert-fixed d-none" role="alert"></div>

    <!-- Form Tambah/Edit Produk -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">Tambah / Edit Produk</div>
        <div class="card-body">
            <form id="produkForm">
                <input type="hidden" name="index" id="index">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama Produk" required>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="kategori" name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Makanan Ringan">Makanan Ringan</option>
                            <option value="Makanan Berat">Makanan Berat</option>
                            <option value="Minuman">Minuman</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control" id="harga" name="harga" placeholder="Harga" required>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-success" id="submitBtn">Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter/Search/Sort -->
    <div class="row mb-3 g-2">
        <div class="col-md-3">
            <select id="filterKategori" class="form-select">
                <option value="">-- Filter Kategori --</option>
                <option value="Makanan Ringan">Makanan Ringan</option>
                <option value="Makanan Berat">Makanan Berat</option>
                <option value="Minuman">Minuman</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" id="searchKeyword" class="form-control" placeholder="Cari Produk">
        </div>
        <div class="col-md-2">
            <select id="sortBy" class="form-select">
                <option value="">Sortir</option>
                <option value="nama">Nama</option>
                <option value="harga">Harga</option>
            </select>
        </div>
        <div class="col-md-4 d-grid">
            <button class="btn btn-warning" id="applyFilter">Terapkan</button>
        </div>
    </div>

    <!-- Tabel Produk -->
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">Daftar Produk</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" id="produkTable">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <nav>
                <ul class="pagination" id="pagination"></ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modal Detail Produk -->
<div class="modal fade" id="detailModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Detail Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detailBody"></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
let currentPage = 1;

function showAlert(message, type='success'){
    const alertBox = $('#alertBox');
    alertBox.removeClass('d-none alert-success alert-danger').addClass('alert-'+type).text(message).fadeIn();
    setTimeout(()=>alertBox.fadeOut(), 2500);
}

function loadProduk(page=1){
    currentPage = page;
    $.get('ajax.php', {
        action:'list',
        kategori: $('#filterKategori').val(),
        search: $('#searchKeyword').val(),
        sort: $('#sortBy').val(),
        page: page
    }, function(res){
        if(res.status=='success'){
            const tbody = $('#produkTable tbody').empty();
            res.data.forEach((p,i)=>{
                let badgeClass = 'bg-secondary';
                if(p.kategori=='Makanan Ringan') badgeClass='bg-success';
                else if(p.kategori=='Makanan Berat') badgeClass='bg-danger';
                else if(p.kategori=='Minuman') badgeClass='bg-info';
                else if(p.kategori=='Lainnya') badgeClass='bg-secondary';
                tbody.append(`
                    <tr>
                        <td>${(page-1)*5 + i +1}</td>
                        <td>${p.nama}</td>
                        <td><span class="badge ${badgeClass} badge-kategori">${p.kategori}</span></td>
                        <td>Rp ${p.harga.toLocaleString('id-ID')}</td>
                        <td>
                            <button class="btn btn-sm btn-info editBtn" data-index="${(page-1)*5 + i}"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger deleteBtn" data-index="${(page-1)*5 + i}"><i class="bi bi-trash"></i></button>
                            <button class="btn btn-sm btn-primary detailBtn" data-index="${(page-1)*5 + i}"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                `);
            });
            // Pagination
            const pag = $('#pagination').empty();
            for(let p=1;p<=res.totalPages;p++){
                pag.append(`<li class="page-item ${p==page?'active':''}"><a class="page-link" href="#">${p}</a></li>`);
            }
        }
    },'json');
}

$(document).ready(function(){
    loadProduk();

    // Submit Form
    $('#produkForm').submit(function(e){
        e.preventDefault();
        $.post('ajax.php', $(this).serialize() + '&action=tambah_edit', function(res){
            showAlert(res.message,res.status=='success'?'success':'danger');
            if(res.status=='success'){
                $('#produkForm')[0].reset();
                $('#submitBtn').text('Tambah');
                $('#index').val('');
                loadProduk(currentPage);
            }
        },'json');
    });

    // Filter/Search/Sort
    $('#applyFilter').click(function(e){ e.preventDefault(); loadProduk(); });

    // Pagination click
    $(document).on('click','#pagination a',function(e){ e.preventDefault(); loadProduk(parseInt($(this).text())); });

    // Delete
    $(document).on('click','.deleteBtn',function(){
        if(confirm('Yakin hapus?')){
            const idx = $(this).data('index');
            $.post('ajax.php',{action:'hapus',index:idx},function(res){
                showAlert(res.message,res.status=='success'?'success':'danger');
                loadProduk(currentPage);
            },'json');
        }
    });

    // Edit
    $(document).on('click','.editBtn',function(){
        const idx = $(this).data('index');
        $.get('ajax.php',{action:'detail',index:idx},function(res){
            if(res.status=='success'){
                $('#nama').val(res.data.nama);
                $('#kategori').val(res.data.kategori);
                $('#harga').val(res.data.harga);
                $('#index').val(idx);
                $('#submitBtn').text('Update');
                $('html, body').animate({scrollTop:0}, 'fast');
            }else showAlert(res.message,'danger');
        },'json');
    });

    // Detail
    $(document).on('click','.detailBtn',function(){
        const idx = $(this).data('index');
        $.get('ajax.php',{action:'detail',index:idx},function(res){
            if(res.status=='success'){
                $('#detailBody').html(`
                    <p><strong>Nama:</strong> ${res.data.nama}</p>
                    <p><strong>Kategori:</strong> ${res.data.kategori}</p>
                    <p><strong>Harga:</strong> Rp ${res.data.harga.toLocaleString('id-ID')}</p>
                `);
                new bootstrap.Modal(document.getElementById('detailModal')).show();
            }else showAlert(res.message,'danger');
        },'json');
    });
});
</script>
</body>
</html>