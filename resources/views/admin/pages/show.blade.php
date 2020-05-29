@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>{{$page->title}}</h2>
            <h3>Category: {{$page->category->name}}</h3>
            <small>Written by: {{$page->user->name}}</small>
            <small>Last update: {{$page->updated_at}}</small>
            <div>
                {{$page->body}}
            </div>
            {{-- @dd($page->tags->count()) --}}
            @if($page->tags->count() > 0)
                <div>
                    <h4>Tags</h4>
                    <ul>
                        @foreach ($page->tags as $tag)
                        <li>{{$tag->name}}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if($page->photos->count() > 0)
                    @foreach ($page->photos as $photo)
                    <img class="img-fluid" src="{{asset('storage/'. $photo->path)}}" alt="{{$photo->name}}">
                    @endforeach
                @endif
        </div>
    </div>
</div>
@endsection
