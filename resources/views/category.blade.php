@extends('layouts.app')

@section('content')
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form class="form" method="POST" id="delete_category">
				@csrf
				<input type="hidden" id="categoryId" name="categoryId" value="">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Delete Confirmation</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<i aria-hidden="true" class="ki ki-close"></i>
					</button>
				</div>
				<div class="modal-body">Are you sure to delete this category?</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary font-weight-bold" id="deletecategory">Save changes</button>
				</div>
			</form>	
		</div>
	</div>
</div>

<div class="modal fade" id="modalRegister" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Category</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
		<form method="POST" enctype="multipart/form-data" action="{{ route('createCategory') }}" id="addCategory">
			@csrf
			<input type="hidden" id="edit_id" name="edit_id" value="">
			<div class="modal-body">
				<div class="form-group">
					<label for="title" class="col-form-label">Category Title</label>
					<input type="text" class="form-control" id="title" name="title" required autocomplete="off"> 
				</div>
				<div class="form-group">
					<input type="checkbox" id="is_child_category" name="is_child_category">
					<label for="is_child_category" class="col-form-label">&nbsp; Is Child Category?</label>
				</div>
				<div class="form-group parentcat" style="display:none;">
					<label for="title" class="col-form-label">Parent Category</label>
					<select class="form-control" name="parent_id" id="parent_id" autocomplete="off">
						<option value="">Please select</option>
						@foreach($category as $val)
							<option value="{{ $val->id }}">{{ $val->title }}</option>
						@endforeach	
					</select>
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
					{{ __('Category') }}
					<span>
						<a href="#" data-toggle="modal" data-target="#modalRegister" style="float:right;text-decoration:none;" id="newCat">Add New Category</a>
					</span>
				</div>

                <div class="card-body">
                    <table class="table table-bordered" id="datatable">
						<thead>
							<tr class="table-primary">
								<th scope="col">#</th>
								<th scope="col">Title</th>
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
  
    var table = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
			type: "POST",
			url : "{{ route('category_list') }}",
			data: {_token : "{{csrf_token()}}"}
		},
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'title', name: 'title'},
			{data: 'status', name: 'status'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });
	
	window.appendCategory = function() {
		$.ajax({
			type: "POST",
			url: "{{ route('categoryOptions') }}",
			data: {_token : "{{csrf_token()}}"},
			success: function (data) {
				$("#parent_id").html(data.options);	
			}
		});
	}
	
	$('#addCategory').validate({
		rules: {	
			title: {
				required: true
			}
		},
		submitHandler: function(form) {
			$(".overlay-loader").css("display","block");
			$("#submit_button").attr("disabled",true);
			
			$.ajax({
				url: $("#addCategory").attr('action'), 
				type: "POST",             
				data: $('#addCategory').serialize(),
				cache: false,             
				processData: false,      
				success: function(response) 
				{
					$(".overlay-loader").css("display","none");
					$("#modalRegister").modal('toggle');
					table.ajax.reload();
					$("#addCategory")[0].reset(); 
					$(".parentcat").hide();
					$("#parent_id").attr('required',false);
					$("#is_child_category").prop('checked',false);
					
					if(response.status == true){
						appendCategory();
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
	
	$(document).on("click", '#deletecategory', function (e) 
	{
		$("#confirmModal").modal("hide");
		
		$.ajax({
			type: "POST",
			url: "{{ route('deleteCategory') }}",
			dataType: 'json',
			data: $("#delete_category").serialize(),
			success: function (data) {
				table.ajax.reload();
				
				$(".parentcat").hide();
				$("#parent_id").attr('required',false);
				$("#is_child_category").prop('checked',false);
				
				if(data.status == true){	
					appendCategory();
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
	
	$(document).on('click',".editCategory",function(){
		var title = $(this).attr('data-titleval');
		var parent_id = $(this).attr('data-parentval');
		var id = $(this).attr('data-id');
		var status = $(this).attr('data-status');
		
		$("input[name=status]").removeAttr('checked');
		$("input[name=status][value="+status+"]").prop('checked',true);
		
		$("#edit_id").val(id);
		$("#title").val(title);
		if(parent_id > 0){
			$(".parentcat").show();
			$("#parent_id").attr('required',true);
			$("#parent_id").val(parent_id);
		} else {
			$(".parentcat").hide();
			$("#parent_id").attr('required',false);
			$("#parent_id").val(parent_id);
			$("#is_child_category").prop('checked',false);
		}
		
		$("#modalRegister").modal('show');
	});
	
	$(document).on('click',"#newCat",function(){
		$("#edit_id").val('');
		$("#title").val('');
		$("#is_child_category").prop('checked',false);
		$(".parentcat").hide();
		$("#parent_id").attr('required',false);
		$("#parent_id").val(parent_id);
		$("input[name=status]").removeAttr('checked');
		$("input[name=status][value='1']").prop('checked', true);
	});
});
  
function fieldU(fieldId, id) {
	$("#" + fieldId).val(id);
}  
</script>
@endsection
