<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use App\Models\Category;
use App\JsonReturn;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
		$category = Category::select('*')->orderBy('id', 'desc')->get();
        return view('category',compact('category'));
    }
	
	public function categorylist(Request $request)
    {
        if ($request->ajax()) 
		{
            $category = Category::select('*')->orderBy('id', 'desc')->get();
			
			$data_arr = array();
			foreach($category as $val)
			{
				$tempData = array(
					'id' => $val->id,
					'title' => $val->title,
					'parent_id' => $val->parent_id,
					'status' => $val->status,
				);
				array_push($data_arr, $tempData);
			}
			
            return Datatables::of($data_arr)
                ->addIndexColumn()
				->addColumn('action', function($row){
					
					$btn = '<a href="#" class="edit btn btn-success editCategory" data-titleval="'.$row['title'].'" data-parentval="'.$row['parent_id'].'" data-id="'.$row['id'].'" data-status="'.$row['status'].'">Edit</a> 
							<button type="button" class="btn btn-primary" onclick="fieldU(\'categoryId\', '.$row["id"].')" data-toggle="modal" data-target="#confirmModal">Delete</button>';
                    return $btn;
                })
				->addColumn('status', function($row){
					if($row['status'] == 1){
						$status = "Active";
					} else {
						$status = "In-Active";
					}
                    return $status;
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }
        return view('category');
    }
	
	public function addCategory(Request $request)
	{
		$edit_id        = ($request->edit_id) ? $request->edit_id : 0;
		$parent_id      = ($request->parent_id) ? $request->parent_id : 0;
		$category_title = ($request->title) ? $request->title : '';
		
		if($edit_id > 0){
			$rules = [
				'title' => 'required|max:255'
			];
			
			$input = $request->only(
				'title'
			);
			
			$validator = Validator::make($input, $rules);
			if ($validator->fails()) {
				return JsonReturn::error($validator->messages());
			}
			
			$category = Category::select('id')->where('title',$category_title)->where('id','!=',$edit_id)->orderBy('id', 'desc')->get();
			if(count($category) > 0){
				$data['status'] = false;
				$data['message'] = 'The title has already been taken.';
				return JsonReturn::success($data);
			}
			
		} else {
			$rules = [
				'title' => 'required|unique:categories|max:255'
			];

			$input = $request->only(
				'title'
			);
			
			$validator = Validator::make($input, $rules);
			if ($validator->fails()) {
				return JsonReturn::error($validator->messages());
			}	
		}
		
		if($request->edit_id > 0)
		{	
			$category = Category::find($request->edit_id);
			$category->title = $request->title;
			$category->parent_id = $parent_id;
			$category->status = $request->status;
			$category->updated_at = date("Y-m-d H:i:s");
			$category->save();
			
			$data['status'] = true;
			$data['message'] = 'Category has been updated succesfully.';
		} 
		else 
		{
			$category = Category::create([
				 'title' => $request->title,
				 'parent_id' => $parent_id,
				 'status' => $request->status,
				 'created_at' => date("Y-m-d H:i:s"),
				 'updated_at' => date("Y-m-d H:i:s")
			]);
			
			$data['status'] = true;
			$data['message'] = 'Category has been created succesfully.';
		}
		
		return JsonReturn::success($data);
	}
	
	public function categoryOptions(Request $request)
	{
		if ($request->ajax()) 
		{
            $category = Category::select('*')->orderBy('id', 'desc')->get();
			
			if(!empty($category))
			{
				$options = '<option value="">Please select</option>';
				foreach($category as $categorydata){
					$options .= '<option value="'.$categorydata->id.'">'.$categorydata->title.'</option>';
				}
				
				$data["options"] = $options;
			} else {
				$options = '<option value="">No category found.</option>';
				$data["options"] = false;
			}	
			
			return JsonReturn::success($data);
        }
	}
	
	public function deleteCategory(Request $request)
	{
		if ($request->ajax()) 
		{
            $Category = Category::find($request->categoryId);
			
			if(!empty($Category))
			{
				$deletechilddata = Category::where('parent_id', $request->categoryId)->delete();
				$deletedata = Category::where('id', $request->categoryId)->delete();
				
				if($deletedata){
					$data["status"] = true;
					$data["message"] = "Category has been deleted succesfully.";
				} else {
					$data["status"] = false;
					$data["message"] = "Something went wrong! Please try again.";
				}		
				
			} else {
				$data["status"] = false;
				$data["message"] = "Something went wrong! Please try again.";
			}	
			
			return JsonReturn::success($data);
        }
	}
}
