<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Type;
use App\Models\User;
use App\Tool\ImageUpload;
use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * 个人作品列表
     */
    public function index(User $user) {
        $data = (new \App\Models\Book())->where('authorId','=',Auth::user()->id)->paginate(10);
        return view('book.myBook',compact('data'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function allBook() {
        $books = Book::all();
        return view('book.allBook',compact('books'));

    }
    public function destroy($bookId) {
        if((new \App\Models\Book)->find($bookId)->author()->id === Auth::user()->id) {
            Book::destroy($bookId);
            session()->flash('删除成功');
        } else {
            session()->flase('没有权限');
        }
        return redirect(route('my_book'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function newBook() {
        $type = new Type();
        $types = $type->all();
        return view('book.new',compact('types'));
    }

    /**
     * @param Request $request
     * @param ImageUpload $upload
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function add(Request $request,User $user)
    {
        if ($request->isMethod('POST')) {
            $data = $request->all();
            $upload = new ImageUpload();
            if ($request->cover) {
                $result = $upload->save($request->cover, 'cover', $user->id, 500);
                if ($result) {
                        $entry = new \App\Models\File();
                        $file = $data['cover'];
                        $entry->mime = $file->getClientMimeType();
                        $entry->original_filename = $file->getClientOriginalName();
                        $entry->filename = $result['filename'];
                        $entry->save();

                        $book = new Book();
                        $book->hits = 0;
                        $book->file_id = $entry->id;
                        $book->title = $request->title;
                        $book->bookIntroduction = $request->introduction;
                        $book->authorName = Auth::user()->name;
                        $book->authorId = Auth::user()->id;
                        $book->cover = $result['path'];
                        $book->typeName = $request->typeName;
                        $book->bookType = 0;

                        $book->save();
                        return redirect()->intended(route('my_book', Auth::user()->id));

                }
            }
        }
    }
/*        $file = $request->file;
        $extension = $file->getClientOriginalExtension();
        Storage::disk('local')->put($file->getFilename().'.'.$extension, File::get($file));
        $entry = new \App\Models\File();
        $entry->mime = $file->getClientMimeType();
        $entry->original_filename = $file->getClientOriginalName();
        $entry->filename = $file->getFilename().'.'.$extension;
        $entry->save();
*/


}
