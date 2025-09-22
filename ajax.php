<?php
header('Content-Type: application/json');
$dataFile = 'produk.json';
$produkList = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
$kategoriList = ['Makanan Ringan','Makanan Berat','Minuman','Lainnya'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action){
    case 'tambah_edit':
        $nama = trim($_POST['nama'] ?? '');
        $kategori = trim($_POST['kategori'] ?? '');
        $harga = floatval($_POST['harga'] ?? 0);
        $index = isset($_POST['index']) ? (int)$_POST['index'] : null;

        if($nama && in_array($kategori, $kategoriList) && $harga>0){
            if($index !== null && isset($produkList[$index])){
                $produkList[$index] = ['nama'=>$nama,'kategori'=>$kategori,'harga'=>$harga];
                $msg = 'Produk berhasil diperbarui!';
            }else{
                $produkList[] = ['nama'=>$nama,'kategori'=>$kategori,'harga'=>$harga];
                $msg = 'Produk berhasil ditambahkan!';
            }
            file_put_contents($dataFile, json_encode($produkList, JSON_PRETTY_PRINT));
            echo json_encode(['status'=>'success','message'=>$msg]);
        }else{
            echo json_encode(['status'=>'error','message'=>'Data produk tidak valid!']);
        }
        break;

    case 'hapus':
        $index = (int)($_POST['index'] ?? -1);
        if(isset($produkList[$index])){
            array_splice($produkList,$index,1);
            file_put_contents($dataFile,json_encode($produkList,JSON_PRETTY_PRINT));
            echo json_encode(['status'=>'success','message'=>'Produk berhasil dihapus!']);
        }else{
            echo json_encode(['status'=>'error','message'=>'Data tidak ditemukan!']);
        }
        break;

    case 'detail':
        $index = (int)($_GET['index'] ?? -1);
        if(isset($produkList[$index])){
            echo json_encode(['status'=>'success','data'=>$produkList[$index]]);
        }else{
            echo json_encode(['status'=>'error','message'=>'Data tidak ditemukan!']);
        }
        break;

    case 'list':
        // Filter/Search/Sort/Pagination
        $filterKategori = $_GET['kategori'] ?? '';
        $searchKeyword = $_GET['search'] ?? '';
        $sortBy = $_GET['sort'] ?? '';
        $page = max(1,(int)($_GET['page'] ?? 1));
        $perPage = 5;

        $displayList = $produkList;
        if($filterKategori) $displayList = array_filter($displayList, fn($p)=>$p['kategori']==$filterKategori);
        if($searchKeyword) $displayList = array_filter($displayList, fn($p)=>stripos($p['nama'],$searchKeyword)!==false);
        if($sortBy=='nama') usort($displayList, fn($a,$b)=>strcmp($a['nama'],$b['nama']));
        if($sortBy=='harga') usort($displayList, fn($a,$b)=>$a['harga']-$b['harga']);

        $totalItems = count($displayList);
        $totalPages = ceil($totalItems/$perPage);
        $displayList = array_slice($displayList, ($page-1)*$perPage, $perPage);

        echo json_encode(['status'=>'success','data'=>array_values($displayList),'totalPages'=>$totalPages]);
        break;

    default:
        echo json_encode(['status'=>'error','message'=>'Action tidak valid!']);
}
