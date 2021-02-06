@extends('layouts.app')

@section('content')
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form class="form" method="POST" id="delete_product">
				@csrf
				<input type="hidden" id="productId" name="productId" value="">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Delete Confirmation</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<i aria-hidden="true" class="ki ki-close"></i>
					</button>
				</div>
				<div class="modal-body">Are you sure to delete this product?</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary font-weight-bold" id="deleteproduct">Save changes</button>
				</div>
			</form>	
		</div>
	</div>
</div>

<div class="modal fade" id="modalRegister" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Product</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
		<form method="POST" enctype="multipart/form-data" action="{{ route('createProduct') }}" id="addProduct">
			@csrf
			<input type="hidden" id="edit_id" name="edit_id" value="">
			<div class="modal-body">
				<div class="form-group">
					<label for="title" class="col-form-label">Select Category</label>
					<select class="form-control" name="category_id" id="category_id" autocomplete="off" required>
						<option value="">Please select</option>
						@foreach($category as $val)
							<option value="{{ $val->id }}">{{ $val->title }}</option>
						@endforeach	
					</select>
				</div>
				<div class="form-group">
					<label for="product_title" class="col-form-label">Product Title</label>
					<input type="text" class="form-control" id="product_title" name="product_title" required autocomplete="off"> 
				</div>
				
				<div class="form-group">
					<label for="product_description" class="col-form-label">Product Description</label>
					<textarea class="form-control" id="product_description" name="product_description" required autocomplete="off" rows="3"></textarea>
				</div>
				
				<div class="form-group">
					<label for="product_image" class="col-form-label">Product Image</label>
					<input type="file" class="form-control" id="product_image" name="product_image" autocomplete="off">
					<br>
					<img src="{{ asset('uploads/products/no_image.jpg') }}" class="product_image_view" style="width: 20%;">
				</div>
				
				<div class="form-group">
					<label for="product_price" class="col-form-label">Product Price</label>
					<input type="text" class="form-control" id="product_price" name="product_price" required autocomplete="off" >
				</div>
				
				<div class="form-group">
					<label for="product_sale_price" class="col-form-label">Product Sale Price</label>
					<input type="text" class="form-control" id="product_sale_price" name="product_sale_price" required autocomplete="off" >
				</div>
				
				<div class="form-group">
					<input type="radio" id="status1" name="status" value="1" checked>
					<label for="status1" class="col-form-label">&nbsp; ACTIVE</label>
					&nbsp;&nbsp;
					<input type="radio" id="status2" name="status" value="0">
					<label for="status2" class="col-form-label">&nbsp; IN ACTIVE</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="submit_button">Save</button>
			</div>
		</form>
    </div>
  </div>
</div>


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
					{{ __('Product') }}
					<span>
						<a href="#" data-toggle="modal" data-target="#modalRegister" style="float:right;text-decoration:none;" id="newCat">Add New Product</a>
					</span>
				</div>

                <div class="card-body">
                    <table class="table table-bordered" id="datatable">
						<thead>
							<tr class="table-primary">
								<th scope="col">#</th>
								<th scope="col">Category</th>
								<th scope="col">Product Title</th>
								<th scope="col">Product Price</th>
								<th scope="col">Product Sale Price</th>
								<th scope="col">Status</th>
								<th scope="col">Action</th>
							</tr>
						</thead>
						<tbody>
							
						</tbody>
					</table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
  $(function () {
	  
	var config = 
	{
		positionX:"top",
		positionY:"center"
	};
	mkNotifications(config);
	var imagepath =  '{{ asset("/uploads/products/") }}';
	
	function readURL4(input) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();
			reader.onload = function(e) {
				$('.product_image_view').attr('src', e.target.result);
			}
			reader.readAsDataURL(input.files[0]);
		}
	}
	$("#product_image").change(function() {
		readURL4(this);
	});
	
    var table = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
			type: "POST",
			url : "{{ route('product_list') }}",
			data: {_token : "{{csrf_token()}}"}
		},
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'category', name: 'category'},
			{data: 'product_title', name: 'product_title'},
			{data: 'product_price', name: 'product_price'},
			{data: 'product_sale_price', name: 'product_sale_price'},
			{data: 'status', name: 'status'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });
	
	$('#addProduct').validate({
		rules: {	
			category_id: {
				required: true
			},
			product_title: {
				required: true
			},
			product_description: {
				required: true
			},
			product_price: {
				required: true
			},
			product_sale_price: {
				required: true
			},
			status: {
				required: true
			}
		},
		submitHandler: function(form) {
			$(".overlay-loader").css("display","block");
			$("#submit_button").attr("disabled",true);
			
			var formData = new FormData($("#addProduct")[0]);
			
			$.ajax({
				url: $("#addProduct").attr('action'), 
				type: "POST",             
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) 
				{
					$(".overlay-loader").css("display","none");
					$("#modalRegister").modal('toggle');
					table.ajax.reload();
					$("#addProduct")[0].reset(); 
					
					if(response.status == true){
						var options = 
						{
							status:"success",
							sound:false,
							duration:1600
						};
						mkNoti(
							"Success",
							response.message,
							options
						);	
						$("#submit_button").attr("disabled",false);			
						return false;
					} else {
						
						var options = 
						{
							status:"danger",
							sound:false,
							duration:5000
						};
						mkNoti(
							"Fail",
							response.message,
							options
						);
						
						$("#submit_button").attr("disabled",false);			
						return false;
					}
				},
				timeout: 10000,
				error: function(e){
					var errors = JSON.parse(e.responseText);
					
					var errorsHtml='';
					$.each(errors.error, function( key, value ) {
						errorsHtml += value[0];
					});
					
					$(".overlay-loader").css("display","none");
					var options = 
					{
						status:"danger",
						sound:false,
						duration:5000
					};
					mkNoti(
						"Fail",
						errorsHtml,
						options
					);
					
					$("#submit_button").attr("disabled",false);			
					return false;
				}
			});
			return false;
		}
	});
	
	$(document).on("click", '#deleteproduct', function (e) 
	{
		$("#confirmModal").modal("hide");
		
		$.ajax({
			type: "POST",
			url: "{{ route('deleteProduct') }}",
			dataType: 'json',
			data: $("#delete_product").serialize(),
			success: function (data) {
				table.ajax.reload();
				if(data.status == true) {
					var options = 
					{
						status:"success",
						sound:false,
						duration:1600
					};
					mkNoti(
						"Success",
						data.message,
						options
					);	
					return false;
				} else {
					var options = 
					{
						status:"success",
						sound:false,
						duration:1600
					};
					mkNoti(
						"Success",
						data.message,
						options
					);	
					return false;
				}	
			},
			timeout: 10000,
			error: function(e){
				var errors = JSON.parse(e.responseText);
				
				var errorsHtml='';
				$.each(errors.error, function( key, value ) {
					errorsHtml += value[0];
				});
				
				$(".overlay-loader").css("display","none");
				var options = 
				{
					status:"danger",
					sound:false,
					duration:5000
				};
				mkNoti(
					"Fail",
					errorsHtml,
					options
				);
			}
		});

	});	
	
	$('input[type="checkbox"]').click(function(){
		if($(this).prop("checked") == true){
			$(".parentcat").show();
			$("#parent_id").attr('required',true);
		} else if($(this).prop("checked") == false){
			$(".parentcat").hide();
			$("#parent_id").attr('required',false);
		}
	});
	
	$(document).on('click',".editProduct",function(){
		var category_id = $(this).attr('data-category_id');
		var product_title = $(this).attr('data-product_title');
		var product_price = $(this).attr('data-product_price');
		var product_sale_price = $(this).attr('data-product_sale_price');
		var product_description = $(this).attr('data-product_description');
		var product_image = $(this).attr('data-product_image');
		var id = $(this).attr('data-id');
		
		var viewpath  =  imagepath+'/'+product_image;
		
		$("#edit_id").val(id);
		$("#category_id").val(category_id);
		$("#product_title").val(product_title);
		$("#product_description").val(product_description);
		$(".product_image_view").attr('src',viewpath);
		$("#product_price").val(product_price);
		$("#product_sale_price").val(product_sale_price);
		
		var status = $(this).attr('data-status');
		$("input[name=status]").removeAttr('checked');
		$("input[name=status][value="+status+"]").prop('checked',true);
		
		$("#modalRegister").modal('show');
	});
	
	$(document).on('click',"#newCat",function(){
		$("#edit_id").val('');
		$("#category_id").val('');
		$("#product_title").val('');
		$("#product_description").val('');
		$(".product_image_view").attr('src',imagepath+'/no_image.jpg');
		$("#product_price").val('');
		$("#product_sale_price").val('');
		$("input[name=status][value=1]").prop('checked',true);
	});
});
  
function fieldU(fieldId, id) {
	$("#" + fieldId).val(id);
}  
</script>
@endsection
