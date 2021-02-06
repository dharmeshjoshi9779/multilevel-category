<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use App\Models\Category;
use App\Models\Products;
use App\Models\File;
use App\JsonReturn;

class ProductsController extends Controller
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
		$category = Category::select('*')->where('status',1)->orderBy('id', 'desc')->get();
        return view('products',compact('category'));
    }
	
	public function productlist(Request $request)
    {
        if ($request->ajax()) 
		{
            $products = Products::select('categories.id as category_id','categories.title','products.id','products.category_id','products.product_title','products.product_description','products.product_image','products.product_price','products.product_sale_price','products.status')->join('categories', 'products.category_id', '=', 'categories.id')->orderBy('id', 'desc')->get();
			
			$data_arr = array();
			foreach($products as $val)
			{
				$tempData = array(
					'id' => $val->id,
					'category_id' => $val->category_id,
					'category' => $val->title,
					'product_title' => $val->product_title,
					'product_description' => $val->product_description,
					'product_image' => $val->product_image,
					'product_price' => $val->product_price,
					'product_sale_price' => $val->product_sale_price,
					'status' => $val->status,
				);
				array_push($data_arr, $tempData);
			}
			
            return Datatables::of($data_arr)
                ->addIndexColumn()
				->addColumn('action', function($row){
					
					$btn = '<a href="#" class="edit btn btn-success editProduct" data-category_id="'.$row['category_id'].'" data-category="'.$row['category'].'" data-product_title="'.$row['product_title'].'" data-product_price="'.$row['product_price'].'" data-product_sale_price="'.$row['product_sale_price'].'" data-product_description="'.$row['product_description'].'" data-product_image="'.$row['product_image'].'" data-id="'.$row['id'].'" data-status="'.$row['status'].'">Edit</a> 
							<button type="button" class="btn btn-primary" onclick="fieldU(\'productId\', '.$row["id"].')" data-toggle="modal" data-target="#confirmModal">Delete</button>';
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
        return view('products');
    }
	
	public function addProduct(Request $request)
	{
		$edit_id        = ($request->edit_id) ? $request->edit_id : 0;
		$category_id    = ($request->category_id) ? $request->category_id : '';
		$product_title  = ($request->product_title) ? $request->product_title : '';
		
		if($edit_id > 0){
			$rules = [
				'category_id' => 'required',
				'product_title' => 'required|max:255',
				'product_description' => 'required|max:500',
				'product_price' => 'required|regex:/^\d{1,13}(\.\d{1,4})?$/',
				'product_sale_price' => 'required|regex:/^\d{1,13}(\.\d{1,4})?$/',
				'status' => 'required',
			];
			
			$input = $request->only(
				'category_id',
				'product_title',
				'product_description',
				'product_price',
				'product_sale_price',
				'status'
			);
			
			$validator = Validator::make($input, $rules);
			if ($validator->fails()) {
				return JsonReturn::error($validator->messages());
			}
			
			$product = Products::select('id')->where('product_title',$product_title)->where('category_id',$category_id)->where('id','!=',$edit_id)->orderBy('id', 'desc')->get();
			if(count($product) > 0){
				$data['status'] = false;
				$data['message'] = 'The title has already been taken.';
				return JsonReturn::success($data);
			}
			
		} else {
			$rules = [
				'category_id' => 'required',
				'product_title' => 'required|max:255',
				'product_description' => 'required|max:500',
				'product_price' => 'required|regex:/^\d{1,13}(\.\d{1,4})?$/',
				'product_sale_price' => 'required|regex:/^\d{1,13}(\.\d{1,4})?$/',
				'status' => 'required',
			];
			
			$input = $request->only(
				'category_id',
				'product_title',
				'product_description',
				'product_price',
				'product_sale_price',
				'status'
			);
			
			$validator = Validator::make($input, $rules);
			if ($validator->fails()) {
				return JsonReturn::error($validator->messages());
			}		
			
			$product = Products::select('id')->where('product_title',$product_title)->where('category_id',$category_id)->orderBy('id', 'desc')->get();
			if(count($product) > 0){
				$data['status'] = false;
				$data['message'] = 'The title has already been taken.';
				return JsonReturn::success($data);
			}
		}
		
		$fileName = '';
		$product_image = $request->file('product_image');
		if($request->hasFile('product_image')){
			$fileName = time().$request->file('product_image')->getClientOriginalName();  
			$request->file('product_image')->move(public_path('uploads/products'), $fileName);	
		}
		
		if($request->edit_id > 0)
		{	
			$products = Products::find($request->edit_id);
			
			$oldImage = $products->product_image;
			
			$products->category_id         = $request->category_id;
			$products->product_title       = $request->product_title;
			$products->product_description = $request->product_description;
			
			if($request->hasFile('product_image')){
				$products->product_image       = $fileName;
				if(file_exists(public_path('uploads/products/'.$oldImage))){
					unlink(public_path('uploads/products/'.$oldImage));
				}
			}
			
			$products->product_price       = $request->product_price;
			$products->product_sale_price  = $request->product_sale_price;
			$products->status              = $request->status;
			$products->updated_at          = date("Y-m-d H:i:s");
			$products->save();
			
			$data['status'] = true;
			$data['message'] = 'Product has been updated succesfully.';
		} 
		else 
		{
			$products = Products::create([
				 'category_id'         => $request->category_id,
				 'product_title'       => $request->product_title,
				 'product_description' => $request->product_description,
				 'product_image'       => $fileName,
				 'product_price'       => $request->product_price,
				 'product_sale_price'  => $request->product_sale_price,
				 'status'              => $request->status,
				 'created_at'          => date("Y-m-d H:i:s"),
				 'updated_at'          => date("Y-m-d H:i:s")
			]);
			
			$data['status'] = true;
			$data['message'] = 'Product has been created succesfully.';
		}
		
		return JsonReturn::success($data);
	}
	
	public function deleteProduct(Request $request)
	{
		if ($request->ajax()) 
		{
            $Product = Products::find($request->productId);
			
			if(!empty($Product))
			{
				$oldImage = $Product->product_image;
				$deletedata = Products::where('id', $request->productId)->delete();
				
				if($deletedata){
					if(file_exists(public_path('uploads/products/'.$oldImage))){
						unlink(public_path('uploads/products/'.$oldImage));
					}
					$data["status"] = true;
					$data["message"] = "Product has been deleted succesfully.";
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
