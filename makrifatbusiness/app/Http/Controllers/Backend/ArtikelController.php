<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\KategoriArtikel;
use Illuminate\Support\Carbon; 
use File; 
use PDF;
// use Illuminate\Support\Facades\Crypt;
// use Barryvdh\DomPDF\Facades as PDF;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ArtikelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        // $artikel = Artikel::all();
        $artikel = Artikel::join('kategori_artikel', 'kategori_artikel.id_ktg', '=', 'artikel.id_ktg')
                   ->orderBy('id_artikel','asc')
                   ->get();
        return view('backend.data_artikel.index', compact('artikel'));
    }

    public function cetak_pdf()
    {
        $artikel = Artikel::join('kategori_artikel', 'kategori_artikel.id_ktg', '=', 'artikel.id_ktg')
                    ->orderBy('id_artikel','desc')
                    ->get();

    	$pdf = PDF::loadview('/cetak_pdf/data_artikel',['artikel'=>$artikel]);
    	return view('backend.data_artikel.cetak_pdf',compact('artikel'));
    }

    public function create()
    {
        $kategori = KategoriArtikel::all();
        return view('backend.data_artikel.create',compact('kategori'));
    }
    public function store(Request $request)
    {
        // rename image name or file name 
        // $getimageName = time().'.'.$request->gambar->getClientOriginalExtension();
        // $request->gambar->move(public_path('data/data_artikel/'), $getimageName);

        // mengambil file gambar dan mengubah namanya 
        if ($request->hasFile('gambar')) {
            $getimageName = rand(11111, 99999) . '.' . $request->file('gambar')->getClientOriginalExtension();
        }

        $data_simpan = [
            'id_ktg' => $request->id_ktg,
            'judul' => $request->judul,
            'tanggal' => $request->tanggal,
            'penulis' => $request->penulis,
            'gambar' => $getimageName,
            'isi' => $request->isi,
        ];

        Artikel::create($data_simpan);
        $upload_success = $request->file('gambar')->move(public_path('data/data_artikel/'), $getimageName);

        return redirect()->route('data_artikel.index')
        ->with('success','Artikel baru berhasil disimpan.')
        ->with('image',$getimageName);
    }

    public function detail($id)
    {
        $artikel = Artikel::join('kategori_artikel', 'kategori_artikel.id_ktg', '=', 'artikel.id_ktg')
                   ->orderBy('id_artikel','asc')
                   ->where('id_artikel',$id)
                   ->get();
        return view('backend.data_artikel.detail',compact('artikel'));
    }
    
    public function edit($id)
    {
        $artikel = Artikel::where('id_artikel',$id)->first();
        $kategori = KategoriArtikel::all();
        return view('backend.data_artikel.edit',compact('artikel','kategori'));
    }

    public function update(Request $request, $id)
    {
        $gbr=$request->nama_gambar;
        
        if($request->has('gambar')) {
            $getimageName = rand(11111, 99999) . '.' . $request->file('gambar')->getClientOriginalExtension();
            $request->gambar->move(public_path('data/data_artikel'), $getimageName);
        }else {
            $getimageName = $gbr;
        }

        $data_simpan = [
            'id_ktg' => $request->id_ktg,
            'judul' => $request->judul,
            'penulis' => $request->penulis,
            'gambar' => $getimageName,
            'isi' => $request->isi,
        ];

        Artikel::where('id_artikel', $id)->update($data_simpan);

        return redirect()->route('data_artikel.index')
                        ->with('success','Data artikel telah berhasil diperbarui');
    }

    public function destroy($id)
    {
        // Mengakses gambar di file dan menghapusnya
        $artikel = Artikel::where('id_artikel',$id)->first();
        File::delete('/data/data_artikel/'.$artikel->gambar);

        // Menghapus data dari database
        Artikel::where('id_artikel',$id)->delete();

        return redirect()->route('data_artikel.index')
                        ->with('success','Data artikel telah berhasil dihapus');
    }
}
