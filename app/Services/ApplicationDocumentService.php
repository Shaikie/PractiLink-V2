<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DocumentType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApplicationDocumentService
{
    public function store(Application $application, DocumentType $type, UploadedFile $file, ?int $userId = null): ApplicationDocument
    {
        $extension=strtolower($file->getClientOriginalExtension()); $mime=$file->getMimeType();
        $rules=$type->allowed_extensions?:['pdf','jpg','jpeg','png','doc','docx']; $mimes=$type->allowed_mime_types?:['application/pdf','image/jpeg','image/png','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if(!in_array($extension,array_map('strtolower',$rules),true)||!in_array($mime,$mimes,true)) throw ValidationException::withMessages(['document'=>'The uploaded file format does not match the allowed document format.']);
        $this->verifySignature($file,$extension);
        $sizeKb=(int)ceil($file->getSize()/1024); if($sizeKb<$type->min_size_kb||$sizeKb>$type->max_size_kb) throw ValidationException::withMessages(['document'=>"The document must be between {$type->min_size_kb} KB and {$type->max_size_kb} KB."]);
        $hash=hash_file('sha256',$file->getRealPath()); $existing=ApplicationDocument::where('application_id',$application->id)->where('sha256',$hash)->first(); if($existing)return $existing;
        $old=ApplicationDocument::where('application_id',$application->id)->where('document_type_id',$type->id)->first(); if($old){Storage::disk($old->disk)->delete($old->path);$old->delete();}
        $storedName=Str::uuid()->toString().'.'.$extension; $path=$file->storeAs('applications/'.$application->id.'/documents',$storedName,'local');
        return ApplicationDocument::create(['application_id'=>$application->id,'document_type_id'=>$type->id,'original_name'=>$file->getClientOriginalName(),'stored_name'=>$storedName,'disk'=>'local','path'=>$path,'extension'=>$extension,'mime_type'=>$mime,'size_bytes'=>$file->getSize(),'sha256'=>$hash,'uploaded_by'=>$userId,'uploaded_at'=>now()]);
    }

    private function verifySignature(UploadedFile $file,string $extension): void
    {
        $handle=fopen($file->getRealPath(),'rb'); $signature=$handle?fread($handle,16):false; if(is_resource($handle))fclose($handle);
        $valid=match($extension){'pdf'=>is_string($signature)&&str_starts_with($signature,'%PDF-'),'jpg','jpeg'=>is_string($signature)&&str_starts_with($signature,"\xFF\xD8\xFF"),'png'=>is_string($signature)&&str_starts_with($signature,"\x89PNG\x0D\x0A\x1A\x0A"),default=>true};
        if(!$valid)throw ValidationException::withMessages(['document'=>'The file content does not match its declared format.']);
    }
}
