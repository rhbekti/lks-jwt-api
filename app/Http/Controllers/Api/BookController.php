<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::latest()->paginate(5);

        return new BookResource(true, 'List Data Books', $books);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required',
            'author'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/books', $image->hashName());

        $book = Book::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'author'   => $request->author,
        ]);

        return new BookResource(true, 'Book has success created!', $book);
    }

    public function show($id)
    {
        $book = Book::find($id);

        return new BookResource(true, 'Detail Book', $book);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'author'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $book = Book::find($id);

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $image->storeAs('public/books', $image->hashName());

            Storage::delete('public/books/' . basename($book->image));

            $book->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'author'   => $request->author,
            ]);
        } else {
            $book->update([
                'title'     => $request->title,
                'author'   => $request->author,
            ]);
        }

        return new BookResource(true, 'Data Book has updated!', $book);
    }

    public function destroy($id)
    {
        $book = Book::find($id);

        //delete image
        Storage::delete('public/books/' . basename($book->image));

        //delete boo$book
        $book->delete();

        //return response
        return new BookResource(true, 'Data Book has deleted!', null);
    }
}
