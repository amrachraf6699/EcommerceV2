@php($title = ($frontendBrand['name'] ?? config('app.name')) . ' - ' . $page->title)

@extends('frontend.layouts.app')

@section('content')
  <section class="pt-36 pb-20 px-6">
    <div class="max-w-4xl mx-auto">
      <div class="reveal mb-10">
        <h1 class="mt-4 text-4xl md:text-6xl font-black">{{ $page->title }}</h1>
      </div>

      <article class="reveal prose prose-lg max-w-none" style="color:var(--white)">
        {!! $page->content !!}
      </article>
    </div>
  </section>
@endsection
