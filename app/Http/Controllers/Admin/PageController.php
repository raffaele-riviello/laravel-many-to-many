<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;
use App\Mail\SendNewMail;
use Illuminate\Support\Facades\Mail;

use App\User;
use App\Page;
use App\Category;
use App\Tag;
use App\Photo;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pages = Page::paginate();
        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        $photos = Photo::all();
        return view('admin.pages.create', compact('categories', 'tags', 'photos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        //dd($request->all());
        //validazione
        $validator = Validator::make($data, [
            'title' => 'required|max:200',
            'body' => 'required',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id'
            // 'photos' => 'required|array',
            // 'photos.*' => 'exists:photos,id' //old system with id
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.pages.create')
            ->withErrors($validator)
                ->withInput();
        }
        //photo storage (upload)
        if (isset($data['photo'])) {
            $path = Storage::disk('public')->put('images', $data['photo']);
            $photo = new Photo;
            $photo->user_id = Auth::id();
            $photo->name = $data['title'];
            $photo->path = $path;
            $photo->description = 'Lorem ipsum';
            $photo->save();
        }

        $page = new Page;
        $data['slug'] = $data['slug'] = Str::slug($data['title'] , '-');
        $data['user_id'] = Auth::id();
        $page->fill($data);
        $saved = $page->save();

        if(!$saved) {
            dd('error');
        }

        $page->tags()->attach($data['tags']);
        if(!empty($photo)) {
            $page->photos()->attach($photo);
        }

        Mail::to('mail@mail.it')->send(new SendNewMail($page));
        return redirect()->route('admin.pages.show', $page->id);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $page = Page::findOrFail($id);
        return view('admin.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $page = Page::findOrFail($id);
        $categories = Category::all();
        $tags = Tag::all();
        $photos = Photo::all();

        return view('admin.pages.edit', compact('page','categories', 'tags', 'photos'));
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
        $page = Page::findOrFail($id);
        $data = $request->all();
        $userId = Auth::id();
        $author =$page->user_id;

        if($userId != $author) {
            abort('404');
        }

        // if there is a new photo
        if (isset($data['photo-file'])) {
            // takes all the photos linked to this page
            $photosThisPage = $page->photos;
            // loop on photos
            foreach($photosThisPage as $photo) {
                // delete the file from the folder
                $deleted = Storage::disk('public')->delete($photo->path);
                // delete the relationship from the database
                $page->photos()->detach($photo->id);
                // look for the record in the photos table
                $photoDb = Photo::find($photo->id);
                // delete the record from photos table
                $photoDb->delete();
            }

            // upload new photo into storage
            $path = Storage::disk('public')->put('images', $data['photo-file']);
            // create new record in the database
            $photo = new Photo;
            $photo->user_id = Auth::id();
            $photo->name = $data['title'];
            $photo->path = $path;
            $photo->description = 'Lorem ipsum';
            $saved = $photo->save();
            // save relationship in bridge table
            $page->photos()->attach($photo->id);
        }

        //validation
        $page->fill($data);
        $updated = $page->update();
        if (!$updated) {
           return redirect()->back();
        }

        //manual deletion of all connections in the bridge table
        //$page->tags()->detach();
        //new creation of the records in the bridge table
        //$page->tags()->attach($data['tags']);

        //synchronize bridge table
        $page->tags()->sync($data['tags']);
        // $page->photos()->sync($data['photos']);

        return redirect()->route('admin.pages.show', $page->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $page = Page::findOrFail($id);

        $page->tags()->detach();
        $page->photos()->detach();
        $deleted = $page->delete();

        if(!$deleted){
            return redirect()->back();
        }

        return redirect()->route('admin.pages.index');
    }
}
