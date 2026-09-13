@extends('layouts.admin')
@section('title',$document->original_name)
@section('page_title','Document')
@section('page_description',$document->documentType->name)
@section('page_actions')<a href="{{ route('student.applications.documents.download',[$application,$document]) }}" class="btn btn-primary"><i class="fas fa-download mr-1"></i> Download</a>@endsection
@section('content')
<div class="row">
    <div class="col-xl-8 mb-3">
        <div class="card h-100"><div class="card-header"><strong>{{ $document->original_name }}</strong></div><div class="card-body">
            @if($document->isPreviewable())
                <iframe class="skeuo-document-preview" src="{{ route('student.applications.documents.preview',[$application,$document]) }}" title="Document preview"></iframe>
            @else
                <div class="skeuo-document-card text-center py-5"><i class="fas fa-file-word fa-3x mb-3"></i><h3>Preview unavailable</h3><p class="text-muted">This file type cannot be rendered safely in the browser. Download it to open it with the appropriate application.</p><a href="{{ route('student.applications.documents.download',[$application,$document]) }}" class="btn btn-primary"><i class="fas fa-download mr-1"></i> Download document</a></div>
            @endif
        </div></div>
    </div>
    <div class="col-xl-4 mb-3"><div class="card"><div class="card-header"><strong>Document information</strong></div><div class="card-body">
        <dl class="mb-0"><dt>Type</dt><dd>{{ $document->documentType->name }}</dd><dt>File</dt><dd class="text-break">{{ $document->original_name }}</dd><dt>Format</dt><dd>{{ strtoupper($document->extension) }}</dd><dt>Size</dt><dd>{{ number_format($document->size_bytes / 1024, 1) }} KB</dd><dt>Uploaded</dt><dd>{{ $document->uploaded_at?->format('d M Y, H:i') }}</dd></dl>
        <a href="{{ route('student.applications.show',$application) }}" class="btn btn-light btn-block mt-3"><i class="fas fa-arrow-left mr-1"></i> Back to application</a>
    </div></div></div>
</div>
@endsection
