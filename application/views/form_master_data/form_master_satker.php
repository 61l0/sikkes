<script type="text/javascript">
 function validasiKode(kode){
	 $.ajax({
		 url: '<?php echo base_url()?>index.php/master_data/master_satker/valid/'+kode,
		 data: '',
		 type: 'GET',
		 beforeSend: function()
		 {},
		 success: function(data)
		 {
			 if(data=='FALSE')
			 {
				 document.getElementById('submit').disabled = true;
				 alert('Maaf, kode '+kode+' telah terdaftar dalam database.');
			 }
			 else
			 {
				 document.getElementById('submit').disabled = false;
			 }
		 }
	 })
 }
</script>
<div id="master">
<div id="judul" class="title">
	Satker
	<!--
	<label class="edit"><a href="#"><img src="<?php echo base_url(); ?>images/icons/Edit_icon.png" /></a></label>
	<label class="detail"><a href="#"><img src="<?php echo base_url(); ?>images/icons/detail.png" /></a></label>
	-->
</div>
<div id="content_master">
	<form class="appnitro" name="form_master_satker" enctype="multipart/form-data" method="post" action="<?php echo base_url().'index.php/master_data/master_satker/save_satker'; ?>">

	<table width="80%" height="25%">
			<tr>
				<td width="10%">Kode Satker</td>
				<td width="70%"><input type="text" name="kdsatker" id="kdsatker" style="width:14%; padding:4px"/><?php echo form_error('kdsatker'); ?></td>
			</tr>
			<tr>
				<td width="10%">Kode Induk</td>
				<td width="70%"><input type="text" name="kdinduk" id="kdinduk" style="width:14%; padding:4px"/><?php echo form_error('kdinduk'); ?></td>
			</tr>
            <tr>
				<td width="10%">Nama Satker</td>
				<td width="70%"><textarea name="nmsatker" id="nmsatker" style="width:70%; padding:4px"  rows="3"/></textarea><?php echo form_error('nmsatker'); ?></td>
			</tr>
             <tr>
				<td width="10%">Departemen</td>
				<td width="70%"><?php $js = 'id="dept" style="padding:3px"'; echo form_dropdown('dept',$option_dept, '024', $js); ?></td>
			</tr>
             <tr>
				<td width="10%">Unit</td>
				<td width="70%"><?php $js = 'id="unit" style="padding:3px"'; echo form_dropdown('unit',$option_unit, null, $js); ?></td>
			</tr>
             <tr>
				<td width="10%">Provinsi</td>
				<td width="70%"><?php $js = 'id="prov" style="padding:3px" onchange="getKab(this.value)"'; echo form_dropdown('prov',$option_prov, null, $js); ?></td>
			</tr>
             <tr>
				<td width="10%">Kabupaten</td>
				<td width="70%"><select id="kabupaten" name="kabupaten" style="padding:3px">
            <option value="0">--- Pilih Provinsi Terlebih Dahulu ---</option></select></td>
			</tr>
            <tr>
				<td width="10%">Nomor SP</td>
				<td width="70%"><input type="text" name="nomorsp" id="nomorsp" style="width:14%; padding:4px" onchange="validasiKode(this.value)"/><?php echo form_error('nomorsp'); ?></td>
			</tr>
            <tr>
				<td width="10%">KPPN</td>
				<td width="70%"><?php $js = 'id="kdkppn" style="padding:3px; width:17%"'; echo form_dropdown('kdkppn',$option_kppn, null, $js); ?></td>
			</tr>
            <tr>
				<td width="10%">Jenis Satker</td>
				<td width="70%"><?php $js = 'id="kdjnssat" style="padding:3px; width:17%"'; echo form_dropdown('kdjnssat',$option_jnssat, null, $js); ?></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<div class="buttons">
						<button type="submit" class="regular" name="save" id="submit">
							<img src="<?php echo base_url(); ?>images/main/save.png" alt=""/>
							Save
						</button>
						<button type="reset" class="negative" name="reset">
							<img src="<?php echo base_url(); ?>images/main/reset.png" alt=""/>
							Reset
						</button>
					</div>
				</td>
			</tr>
            <tr>
                <td>
                    <div class="buttons">
                        <a href="<?php echo base_url();?>index.php/master_data/master_satker/grid_daftar"><img src="<?php echo base_url(); ?>images/main/back.png" alt=""/>Back</a>
                    </div>
                </td>
            </tr>
	</table>
	</form>
</div>
</div>

<script type="text/javascript">
function getKab(v)
{
	//var kdProv = document.getElementById('provinsi').value;
	var url = '<?php echo base_url()?>index.php/e-planning/pendaftaran/get_kab/'+v;
	//alert(v)
	
	$.ajax({
		//alert('test')
		url: url,
		data: '',
		type: 'GET',
		dataType: 'json',
		beforeSend: function()
		{
		},
		success: function(data)
		{
			var id;
			var option;
			var nama;
			
			//$('#testing').html('');
			
			$('#kabupaten').html('<option>--- Pilih Kabupaten ---</option>');
			
			for(var i=0; i< data.length;i++)
			{
				id = data[i]['KodeKabupaten'];
				nama = data[i]['NamaKabupaten'];
				option += '<option value="'+id+'">'+nama+'</option>';
			}
  				$('#kabupaten').append(option);
		}
	});	
}
</script>
