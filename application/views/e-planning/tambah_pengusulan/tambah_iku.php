<div id="kiri">
<div id="judul" class="title">
	Tambah IKU
</div>
<div id="content">
		<form id="form_cari_fungsi" class="appnitro" enctype="multipart/form-data" method="post" action="<?php echo base_url().'index.php/e-planning/pendaftaran/cari_iku'; ?>">
			<p>search </p>
			<input type="hidden" id="KodeFungsi" name="KodeFungsi" value="<?php echo $KodeFungsi; ?>" />
			<input type="hidden" id="KodeSubFungsi" name="KodeSubFungsi" value="<?php echo $KodeSubFungsi; ?>" />
			<input type="hidden" id="KodePengajuan" name="KodePengajuan" value="<?php echo $KodePengajuan; ?>" />
			<input type="hidden" id="KodeProgram" name="KodeProgram" value="<?php echo $KodeProgram; ?>" />
			<input id="keyword" name="keyword" /> 
			<select name="kategori" id="kategori">
				<option value="KodeIku">Kode IKU</option>
				<option value="Iku">IKU</option>
			</select>
			<input type="submit" value="search" />
		</form>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><p>No</p></td>
				<td><p>Kode IKU</p></td>
				<td><p>IKU</p></td>
				<td><p>Jumlah</p></td>
				<td><p>Action</p></td>
			</tr>
			<?php
			if($iku!=NULL){
				$no=1;
				foreach($iku->result() as $row){
					$kode = $row->KodeIku;
					$nama = $row->Iku; 
			?>
			<tr onmouseover="this.style.backgroundColor='#ffff00';" onmouseout="this.style.backgroundColor='#ffffff';">
				<form id="form_tambah_fungsi" class="appnitro" enctype="multipart/form-data" method="post" action="<?php echo base_url().'index.php/e-planning/pendaftaran/save_iku'; ?>">
				<input type="hidden" id="KodeFungsi" name="KodeFungsi" value="<?php echo $KodeFungsi; ?>" />
				<input type="hidden" id="KodeSubFungsi" name="KodeSubFungsi" value="<?php echo $KodeSubFungsi; ?>" />
				<input type="hidden" id="KodePengajuan" name="KodePengajuan" value="<?php echo $KodePengajuan; ?>" />
				<input type="hidden" id="KodeProgram" name="KodeProgram" value="<?php echo $KodeProgram; ?>" />
				<td><?php echo $no; ?></td>
				<td><?php echo $kode; ?><input type="hidden" id="kode" name="kode" value="<?php echo $kode; ?>" /></td>
				<td><?php echo $nama; ?><input type="hidden" id="nama" name="nama" value="<?php echo $nama; ?>" /></td>
				<td><input id="jumlah" name="jumlah" value="" /></td>
				<td><input type="submit" value="Tambah" /></td>
				</form>
			</tr>
			<?php 
					$no++;	
				} 
			}
			?>
		</table>
	</div>
</div>
<?php if(form_error('jumlah')!=''){ ?>
	<script type="text/javascript">
		alert("<?php echo form_error('jumlah'); ?>");
	</script>
<?php } ?>