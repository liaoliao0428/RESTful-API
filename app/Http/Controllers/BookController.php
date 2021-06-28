<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\BookResource;

class BookController extends Controller
{

    public function __construct()
    {
        $this->middleware('scopes:create-books', ['only' => ['store']]);
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }

    /**
     *      @OA\Get(
     *      path="/api/books",
     *      operationId="bookIndex",
     *      tags={"Book"},
     *      summary="取得書資源列表",
     *      description="查看書資源列表",
     *      security={
     *         {
     *              "passport": {}
     *         }
     *      },
     *      @OA\Parameter(
     *          name="filters",
     *          description="篩選條件",
     *          required=false,
     *          in="query",
     *          example="name:歷險",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sorts",
     *          description="排序條件",
     *          required=false,
     *          in="query",
     *          example="name:asc,id:desc",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          description="設定回傳資料筆數(預設10筆資料)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="請求成功"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="身分驗證未通過"
     *      ),
     *      
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 10;

        $query = Book::query();
        
        if(isset($request->sorts)) {
            $sorts = explode(',', $request->sorts);
            foreach ($sorts as $key => $sort) {
                list($key, $value) = explode(':', $sort);
                if($value == 'asc' || $value == 'desc'){
                    $query->orderBy($key, $value);
                }
            }
        }else{
            if(isset($request->filters)){
                $filters = explode(',', $request->filters);
                foreach ($filters as $key => $filter) {
                    list($key, $value) = explode(':', $filter);
                    $query->where($key, 'like', "%$value%");
                }
            }
            $query->orderBy('id', 'desc');
        }

        $books = $query->paginate($limit)->appends($request->query());

        return response(['data' => $books], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**     
     *      @OA\Post(
     *      path="/api/books",
     *      operationId="bookStore",
     *      tags={"Book"},
     *      summary="新增書本資料",
     *      description="新增書本資料",
     *      security={
     *         {
     *              "passport": {}
     *         }
     *      },
     *      @OA\RequestBody(
     *           @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="Book",
     *                  required={"type_id", "name", "writer"},
     *                  @OA\Property(property="type_id",type="integer",description="書本分類",example=1),
     *                  @OA\Property(property="name",type="string",description="書本名稱",example="哈利波特"),
     *                  @OA\Property(property="writer",type="string",description="作者",example="jk.羅琳"),
     *                  @OA\Property(property="publishdate",type="date",description="出版日期",example="2000-05-05"),
     *                  @OA\Property(property="summary",type="text",description="簡介",example="魔法故事"),
     *              )              
     *          )                    
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="請求成功"
     *         )
     *          
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="身分驗證未通過"
     *      )
     * )
     *  
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'type_id' => 'required|exists:types,id',
            'name' => 'required|string',
            'writer' => 'required|string',
            'publishdate' => 'nullable|date',
            'summary' => 'nullable',
        ]);

        $book = Book::create($request->all());
        $book = $book->refresh();
        // return response($book, Response::HTTP_CREATED);
        return new BookResource($book);
    }

    /**
     * 
     * @OA\Get(
     *      path="/api/books/{id}",
     *      operationId="bookShow",
     *      tags={"Book"},
     *      summary="查看單一書本資源",
     *      description="查看單一書本資源",
     *      @OA\Parameter(
     *          name="id",
     *          description="Book id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      security={
     *         {
     *              "passport": {}
     *         }
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="請求成功",          
     *     ),
     *      @OA\Response(
     *          response=401,
     *          description="身分驗證未通過"
     *      ),
     * )
     * 
     * Display the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function show(Book $book)
    {
        return new BookResource($book);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * @OA\Patch(
     *      path="/api/books/{id}",
     *      operationId="bookUpdate",
     *      tags={"Book"},
     *      summary="更新書本資料",
     *      description="更新書本資料",
     *      @OA\Parameter(
     *          name="id",
     *          description="Book id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *           @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  schema="Book",
     *                  required={"type_id", "name", "writer"},
     *                  @OA\Property(property="type_id",type="integer",description="書本分類",example=1),
     *                  @OA\Property(property="name",type="string",description="書本名稱",example="哈利波特"),
     *                  @OA\Property(property="writer",type="string",description="作者",example="jk.羅琳"),
     *                  @OA\Property(property="publishdate",type="date",description="出版日期",example="2000-05-05"),
     *                  @OA\Property(property="summary",type="text",description="簡介",example="魔法故事"),
     *              )              
     *          )                    
     *      ),
     *      security={
     *         {
     *              "passport": {}
     *         }
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="請求成功",
     *          
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="找不到資源"
     *       )
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Book $book)
    {
        $this->validate($request, [
            'type_id' => 'required|exists:types,id',
        ]);

        $book->update($request->all());
        return response($book, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/api/books/{id}",
     *      operationId="bookDelete",
     *      tags={"Book"},
     *      summary="刪除書本資料",
     *      description="刪除動物資料",
     *      @OA\Parameter(
     *          name="id",
     *          description="Book id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )     
     *      ),
     *      security={
     *          {
     *              "passport":{}
     *          }
     *      },
     *      @OA\Response(
     *          response=204,
     *          description="刪除回傳空直"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="找不到資源"
     *      ), 
     *
     * )
     *
     * Remove the specified resource from storage.
     * 
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
    {
        $book->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
