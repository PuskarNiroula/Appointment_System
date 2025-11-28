@extends('Layout.layout')

@section("page-title")
    Post
@endsection

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
     <a href="/posts/create" class="btn btn-primary">Create Post </a>
    </div>

    @if(!empty($posts) && $posts->count())

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Posts Table</h5>
            </div>

            <div class="card-body">
                <table class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Post Name</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($posts as $index => $post)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $post->name }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>

    @else
        <div class="alert alert-info">No posts available.</div>
    @endif

@endsection
