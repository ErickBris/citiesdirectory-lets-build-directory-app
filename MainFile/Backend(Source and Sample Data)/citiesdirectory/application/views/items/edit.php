			<?php
			$this->lang->load('ps', 'english');
			?>
			<ul class="breadcrumb">
				<li><a href="<?php echo site_url() . "/dashboard";?>"><?php echo $this->lang->line('dashboard_label')?></a> <span class="divider"></span></li>
				<li><a href="<?php echo site_url('items');?>"><?php echo $this->lang->line('item_list_label')?></a> <span class="divider"></span></li>
				<li><?php echo $this->lang->line('update_item_label')?></li>
			</ul>
			<div class="wrapper wrapper-content animated fadeInRight">
			<?php
			$attributes = array('id' => 'item-form','enctype' => 'multipart/form-data');
			echo form_open(site_url("items/edit/".$item->id), $attributes);	
			
			?>
			
				<legend><?php echo $this->lang->line('item_info_label')?></legend>
					
				<div class="row">
					<div class="col-sm-6">
							<div class="form-group">
								<label><?php echo $this->lang->line('item_name_label')?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('item_name_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" placeholder="<?php echo $this->lang->line('item_name_label')?>" name='name' id='name'
								 value="<?php echo $item->name;?>">
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('cat_label')?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('cat_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<select class="form-control" name="cat_id" id="cat_id">
								<?php
									foreach($this->category->get_all($this->city->get_current_city()->id)->result() as $cat){
										echo "<option value='".$cat->id."'";
										if($item->cat_id == $cat->id) 
											echo " selected ";
										echo ">".$cat->name."</option>";
									}
								?>
								</select>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('sub_cat_label')?>
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('sub_cat_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<select class="form-control" name="sub_cat_id" id="sub_cat_id">
								<option><?php echo $this->lang->line('select_sub_cat_message')?></option>
								<?php
									foreach($this->sub_category->get_all_by_cat_id($item->cat_id)->result() as $sub_cat){
										echo "<option value='".$sub_cat->id."'";
										if($item->sub_cat_id == $sub_cat->id) 
											echo " selected ";
										echo ">".$sub_cat->name."</option>";
									}
								?>
								</select>
							</div>

							<div class="form-group">
								<label><?php echo $this->lang->line('phone_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('phone_tooltips')?>">
								    	<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" name="phone" placeholder="<?php echo $this->lang->line('phone_label')?>" value="<?php echo $item->phone;?>"/>
							</div>

							<div class="form-group">
								<label><?php echo $this->lang->line('email_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('email_tooltips')?>">
								    	<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" name="email" placeholder="<?php echo $this->lang->line('email_label')?>" value="<?php echo $item->email;?>"/>
							</div>

							<div class="form-group">
								<label><?php echo $this->lang->line('lat_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('lat_tooltips')?>">
								    	<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" name="lat" placeholder="<?php echo $this->lang->line('lat_label')?>" value="<?php echo $item->lat;?>"/>
							</div>

							<div class="form-group">
								<label><?php echo $this->lang->line('lng_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('lng_tooltips')?>">
								    	<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" name="lng" placeholder="<?php echo $this->lang->line('lng_label')?>" value="<?php echo $item->lng;?>"/>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('publish_label')?>
								<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('publish_tooltips')?>">
									<span class='glyphicon glyphicon-info-sign menu-icon'>
								</a>
								: 
								</label>
								<?php
									echo form_checkbox("is_published",$item->is_published,$item->is_published);
								 ?>
							</div>
						</div>
						
						<div class="col-sm-6">
					
							<div class="form-group">
								<label><?php echo $this->lang->line('search_tag_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('search_tag_tooltips')?>">
								    	<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<input class="form-control" type="text" name="search_tag" placeholder="<?php echo $this->lang->line('search_tag_label')?> " value="<?php echo $item->search_tag; ?>"/>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('description_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('item_description_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<textarea class="form-control" name="description" placeholder="<?php echo $this->lang->line('description_label')?>" rows="8"><?php echo $item->description;?></textarea>
							</div>
							
							<div class="form-group">
								<label><?php echo $this->lang->line('address_label')?> 
									<a href="#" class="tooltip-ps" data-toggle="tooltip" title="<?php echo $this->lang->line('address_tooltips')?>">
										<span class='glyphicon glyphicon-info-sign menu-icon'>
									</a>
								</label>
								<textarea class="form-control" name="address" placeholder="<?php echo $this->lang->line('address_label')?>" rows="8"><?php echo $item->address;?></textarea>
							</div>
					</div>
				</div>
				
				<input type="submit" value="<?php echo $this->lang->line('update_button')?>" class="btn btn-primary"/>
				<a class="btn btn-primary" href="<?php echo site_url('items/gallery/'.$item->id);?>"><?php echo $this->lang->line('goto_gallery_button')?></a>
				<a href="<?php echo site_url('items');?>" class="btn btn-primary"><?php echo $this->lang->line('cancel_button')?></a>
			</form>
			</div>
			<script>
				$(document).ready(function(){
					$('#item-form').validate({
						rules:{
							name:{
								required: true,
								minlength: 4,
								remote: {
									url: '<?php echo site_url("items/exists/".$item->id);?>',
								  	type: "GET",
								  	data: {
								  		name: function () {
								  			return $('#name').val();
								  		},
								    	sub_cat_id: function() {
								    		return $('#sub_cat_id').val();
								    	}
								  	}
								}
							},
							unit_price: {
								number: true
							}
						},
						messages:{
							name:{
								required: "Please fill item name.",
								minlength: "The length of item name must be greater than 4",
								remote: "item name is already existed in the system"
							},
							unit_price: {
								number: "Only number is allowed."
							}
						}
					});
					
					$('#cat_id').change(function(){
						var catId = $(this).val();
						$.ajax({
							url: '<?php echo site_url('items/get_sub_cats');?>/'+catId,
							method: 'GET',
							dataType: 'JSON',
							success:function(data){
								$('#sub_cat_id').html("");
								$.each(data, function(i, obj){
								    $('#sub_cat_id').append('<option value="'+ obj.id +'">' + obj.name + '</option>');
								});
								$('#name').val($('#name').val() + " ").blur();
							}
						});
					});
					
					$('#sub_cat_id').on('change', function(){
						$('#name').val($('#name').val() + " ").blur();
					});
					
					$(function () { $("[data-toggle='tooltip']").tooltip(); });
				});
			</script>

