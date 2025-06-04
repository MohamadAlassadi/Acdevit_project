<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // جلب المقالات مع البيانات المرتبطة بالكاتب
        $articles = Article::with('creator')->get()->map(function ($article) {
            return [
                'id' => $article->Article_id,
                'title' => $article->Title,
                'content' => $article->Content,
                'image' => $article->Image ? asset(  $article->Image) : null,
                'catigory' => $article->catigory,  // جلب تصنيف المقال
                'created_by' => $article->creator ? $article->creator->Name : null,  // لو حابب تعرض اسم الكاتب
            ];
        });

        return response()->json($articles); 
    }
    public function searchByTitle($term)
    {
        $articles = Article::where('Title', 'LIKE', $term . '%')->get();
    
        return response()->json($articles);
    }
    

    // باقي الكود...



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // هذا المكان يمكن أن يكون لإنشاء المقالات
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'catigory' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // إنشاء المقالة الجديدة
        $article = new Article();
        $article->Title = $validated['title'];
        $article->Content = $validated['content'];
        $article->catigory = $validated['catigory'];
        // لاحظ: حذفنا تعيين Created_by لأنه الديفولت في الداتا بيز هو NULL
    
        // رفع الصورة إن وجدت
        if ($request->hasFile('image')) {
            \Log::info('في صورة مرفوعة.');
        
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName(); // اسم محفوظ + أصلي
        
            $image->move(public_path('uploads/images'), $imageName); // حفظ في public مباشرة
        
            $article->Image = 'uploads/images/' . $imageName;
        }
        
        
    
        $article->save();
    
        return response()->json([
            'message' => 'تم إنشاء المقالة بنجاح',
            'article' => [
                'id' => $article->Article_id,
                'title' => $article->Title,
                'content' => $article->Content,
                'image' => $article->Image ? asset($article->Image) : null,
                'catigory' => $article->catigory,
                'created_by' => $article->Created_by,
            ],
        ], 201);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        // عرض تفاصيل مقال معين
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        // مكان لتعديل المقالات
    }

    public function destroy($id)
    {
        $article = Article::find($id);
    
        if (!$article) {
            return response()->json(['message' => 'المقالة غير موجودة'], 404);
        }
    
        $article->delete();
    
        return response()->json(['message' => 'تم حذف المقالة بنجاح']);
    }
    
   public function update(Request $request, $id)
{
    $article = Article::find($id);

    if (!$article) {
        return response()->json(['message' => 'المقالة غير موجودة'], 404);
    }

    $validated = $request->validate([
        'Title' => 'sometimes|string|max:255',
        'Content' => 'sometimes|string',
        'catigory' => 'sometimes|string',
        'Image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // نتحقق من نوع الصورة وحجمها
    ]);

    // إذا تم رفع صورة جديدة
    if ($request->hasFile('Image')) {
        $image = $request->file('Image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads/images'), $imageName);
        $validated['Image'] = 'uploads/images/' . $imageName;
    }

    $article->update($validated);

    return response()->json([
        'message' => 'تم تحديث المقالة بنجاح',
        'article' => $article
    ]);
}


}
