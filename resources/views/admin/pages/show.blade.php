@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h2>{{$page->title}}</h2>
            <h3>Categoty: {{$page->category->name}}</h3>
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
        </div>
    </div>
</div>
@endsection
