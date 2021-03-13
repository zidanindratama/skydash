<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Tag;
use App\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class PostController extends Controller
{
    public function __construct(){
        $this->middleware([
            'auth',
            'privilege:Administrator',
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::paginate(10);
        return view('dashboard.post.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ];

        return view('dashboard.post.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required',
            'content' => 'required',
            'category_id' => 'required',
        ]);

        $gambar = $request->gambar;
        $new_gambar = time().$gambar->getClientOriginalName();

        $post = Post::create([
            'judul' => $request->judul,
            'slug' => Str::slug($request->judul),
            'content' => $request->content,
            'category_id' => $request->category_id,
            'user_id' => Auth::id(),
            'gambar' => 'skydash/img/uploads/'.$new_gambar,
        ]);

        $post->tags()->attach($request->tags);

        $gambar->move('skydash/img/uploads/', $new_gambar);
        
        if($post){
            Alert::success('Berhasil','Data posts berhasil ditambah');
        }
        return redirect('/dashboard/post'); 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
         $data = [
            'categories' => Category::all(),
            'tags' => Tag::all(),
            'post' => Post::findOrFail($id),
        ];

        return view('dashboard.post.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $post = Post::findorfail($id);

        if ($request->has('gambar')) {
            $gambar = $request->gambar;
            $new_gambar = time().$gambar->getClientOriginalName();
            $gambar->move('skydash/img/uploads/', $new_gambar);

            $post_data = [
                'judul' => $request->judul,
                'category_id' =>  $request->category_id,
                'content' =>  $request->content,
                'gambar' => 'skydash/img/uploads/'.$new_gambar,
                'slug' => Str::slug($request->judul),
                'user_id' => Auth::id(),
            ];
        }
        else{
            $post_data = [
                'judul' => $request->judul,
                'category_id' =>  $request->category_id,
                'content' =>  $request->content,            
                'slug' => Str::slug($request->judul),
                'user_id' => Auth::id(),
            ];
        }
    
        $validasi = [
            'post_tag' => $post->tags()->sync($request->tags),
            'post_data' => $post->update($post_data),
        ];

        if($validasi){
            Alert::success('Berhasil','Data posts berhasil diubah');
        }
        return redirect('/dashboard/post'); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $validasi = $post->delete($post);
        if($validasi) :
            Alert::success('Berhasil!', 'Data post berhasil dihapus');
        endif;
        return redirect('/dashboard/post');
    }

    public function tampil_hapus() {
        $posts = Post::onlyTrashed()->paginate(10);
        return view('dashboard.post.delete', compact('posts'));
    }

    public function restore($id) {
        $post = Post::withTrashed()->where('id', $id)->first();
        $validasi = $post->restore();
        if($validasi) :
            Alert::success('Berhasil!', 'Data post berhasil direstore');
        endif;
        return redirect('/dashboard/post');
    }

    public function kill($id) {
        $post = Post::withTrashed()->where('id', $id)->first();
        $validasi = $post->forceDelete();
        if($validasi) :
            Alert::success('Berhasil!', 'Data post berhasil dihapus permanen');
        endif;
        return redirect('/dashboard/post/hapus');
    }
}
