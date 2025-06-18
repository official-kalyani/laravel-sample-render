<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resume;
use Smalot\PdfParser\Parser;
use App\Helpers\ResumeParser;
use Illuminate\Support\Facades\Storage;

class ResumeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'resume' => 'required|file|mimes:pdf|max:5120',
        ]);

        $file = $request->file('resume');
        $path = $file->store('resumes');

        $pdf = (new Parser())->parseFile($file->getPathname());
        $text = $pdf->getText();

        $data = ResumeParser::parse($text);

        $resume = Resume::create([
            'user_id' => $request->user()->id,
            'file_path' => $path,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'education' => $data['education'],
            'skills' => $data['skills'],
            'experience' => $data['experience'],
        ]);

        return response()->json($resume, 201);
    }

    public function index(Request $request)
    {
        return Resume::where('user_id', $request->user()->id)->get();
    }

    public function show(Request $request, $id)
    {
        $resume = Resume::where('user_id', $request->user()->id)->findOrFail($id);
        return response()->json($resume);
    }

    public function destroy(Request $request, $id)
    {
        $resume = Resume::where('user_id', $request->user()->id)->findOrFail($id);
        Storage::delete($resume->file_path);
        $resume->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function search(Request $request)
    {
        $skill = $request->query('skill');
        $query = Resume::where('user_id', $request->user()->id);

        if ($skill) {
            $query->where('skills', 'LIKE', "%{$skill}%");
        }

        return response()->json($query->get());
    }
}
