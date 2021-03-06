<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Laporan_monitoring extends CI_Controller 
{
	function __construct(){
		parent::__construct();
		$this->cek_session();
		$this->load->helper('fusioncharts');
		$this->load->model('e-monev/laporan_monitoring_model','lmm');
		$this->load->model('e-monev/laporan_monitoring_model2','lmm2');
		$this->load->model('e-monev/bank_model','bm');
		$this->load->model('e-monev/feedback_emonev_model','fm');
		$this->load->model('role_model');
	}
	
	function cek_session()
	{	
		$kode_role = $this->session->userdata('kd_role');
		if($kode_role == '')
		{
			redirect('login/login_ulang');
		}
	}
	
	function index()
	{
		$this->grid();
	}
	
	//nampilin grid
	function grid()
	{
		$kode_role = $this->session->userdata('kd_role');
		$colModel['no'] = array('No',20,TRUE,'center',0);
		if($kode_role != Role_model::PEMBUAT_LAPORAN) $colModel['t_satker.nmsatker'] = array('Nama Satker',300,TRUE,'center',1);
		$colModel['d_kmpnen.urkmpnen'] = array('Komponen',350,TRUE,'center',1);
		$colModel['d.urskmpnen'] = array('Sub Komponen',330,TRUE,'center',1);
		$colModel['realisasi_fisik_kontrak'] = array('Realisasi Fisik Kontraktual',135,TRUE,'center',0);
		$colModel['realisasi_fisik_swakelola'] = array('Realisasi Fisik Swakelola',135,TRUE,'center',0);
		$colModel['PERMASALAHAN'] = array('Permasalahan',80,FALSE,'center',0);
		$colModel['LAPORAN'] = array('Laporan',50,FALSE,'center',0);
		$colModel['GRAFIK'] = array('Grafik',50,FALSE,'center',0);
		$colModel['UNGGAH_DOK'] = array('Unggah Dokumen',90,FALSE,'center',0);

		if($this->session->userdata('hal_monitoring') != '' && $this->session->userdata('rp_monitoring') != ''){
			$hal = $this->session->userdata('hal_monitoring');
			$rp = $this->session->userdata('rp_monitoring');
			$gridParams = array(
							'width' => 'auto',
							'height' => 500,
							'rp' => $rp,
							'rpOptions' => '[15,30,50,100]',
							'pagestat' => 'Menampilkan : {from} ke {to} dari {total} data.',
							'blockOpacity' => 0,
							'title' => '',
							'newp' => $hal,
							'showTableToggleBtn' => false,
							'nowrap' => false
							);
		}else{
		$gridParams = array(
							'width' => 'auto',
							'height' => 500,
							'rp' => 15,
							'rpOptions' => '[15,30,50,100]',
							'pagestat' => 'Menampilkan : {from} ke {to} dari {total} data.',
							'blockOpacity' => 0,
							'title' => '',
							'showTableToggleBtn' => false,
							'nowrap' => false
							);
		}
		//menambah tombol pada flexigrid top toolbar
		$buttons[] = array('Cetak','print','spt_js');
		// $buttons[] = array('Tambah','add','spt_js');
		// $buttons[] = array('Hapus','delete','spt_js');
		// $buttons[] = array('separator');
		// $buttons[] = array('Pilih Semua','add','spt_js');
		// $buttons[] = array('separator');
		// $buttons[] = array('Hapus Pilihan','delete','spt_js');
		// $buttons[] = array('separator');	
		
		// mengambil data dari file controler ajax pada method grid_user	
		$url = site_url()."/e-monev/laporan_monitoring/grid_data_monitoring";
		$grid_js = build_grid_js('user',$url,$colModel,'ID','asc',$gridParams,$buttons,true);
		//$grid_js = build_grid_js('user',$url,$colModel,'ID','asc',$gridParams,'',true);
		$data['js_grid'] = $grid_js;
		$data['added_js'] = 
		"<script type='text/javascript'>	
		$(window).bind('unload', function() {
			var rp = document.getElementsByName('rp')[0].value;
			var page = $('.pcontrol input').val();
			$.ajax({
				type: 'POST',
				url:  '".site_url()."/e-monev/laporan_monitoring/save_halaman/',
				async : false,
				data:{
					rp:rp,
					page:page
				}
			});
		});
		function spt_js(com,grid){
			if (com=='Cetak')
			{
				location.href= '".site_url()."/e-monev/laporan_monitoring/form_pilih_satker/';    
			}
			if (com=='Pilih Semua')
			{
				$('.bDiv tbody tr',grid).addClass('trSelected');
			}
			if (com=='Tambah'){
				location.href= '".base_url()."index.php/laporan_monitoring/add';    
			}
			if (com=='Hapus Pilihan')
			{
				$('.bDiv tbody tr',grid).removeClass('trSelected');
			}
			if (com=='Hapus')
				{
				   if($('.trSelected',grid).length>0){
					   if(confirm('Anda yakin ingin menghapus ' + $('.trSelected',grid).length + ' buah data?')){
							var items = $('.trSelected',grid);
							var itemlist ='';
							for(i=0;i<items.length;i++){
								itemlist+= items[i].id.substr(3)+',';
							}
							$.ajax({
							   type: 'POST',
							   url: '".site_url('/laporan_monitoring/delete')."',
							   data: 'items='+itemlist,
							   success: function(data){
								$('#user').flexReload();
								alert(data);
							   }
							});
						}
					} else {
						return false;
					} 
				}        
		} </script>";
		$data['notification'] = "";
		if($this->session->userdata('notification')!=''){
			$data['notification'] = "
				<script>
					$(document).ready(function() {
						$.growlUI('Pesan :', '".$this->session->userdata('notification')."');
					});
				</script>
			";
		}//end if
			
		//$data['added_js'] variabel untuk membungkus javascript yang dipakai pada tombol yang ada di toolbar atas
		$data['judul'] = 'Laporan Monitoring';
		$this->session->unset_userdata('hal_monitoring');
		$this->session->unset_userdata('rp_monitoring');

		$data['content'] = $this->load->view('grid',$data,true);
		$this->load->view('main',$data);
	} 

	//isi data grid monitoring
	function grid_data_monitoring() 
	{
		$tgl_atas = $this->lmm->get_referensi_by_id(2)->row()->tanggal;
		$tgl_tengah = $this->lmm->get_referensi_by_id(1)->row()->tanggal;
		$tgl_bawah = $this->lmm->get_referensi_by_id(3)->row()->tanggal;
		$realisasi_fisik_kontrak = 0;
		$realisasi_fisik_swakelola = 0;
		$kd_role = $this->session->userdata('kd_role');
		$valid_fields = array('d.urskmpnen','t_satker.nmsatker','d_kmpnen.urkmpnen');
		$this->flexigrid->validate_post('d.kdsatker','asc',$valid_fields);
		$records = $this->lmm->get_sub_komponen();	
		$this->output->set_header($this->config->item('json_header'));
		$no = 0;
		foreach ($records['records']->result() as $row){
				$no = $no+1;
				$thang = $row->thang;
				$kdjendok = $row->kdjendok;
				$kdsatker = $row->kdsatker;
				$kddept = $row->kddept;
				$kdunit = $row->kdunit;
				$kdprogram = $row->kdprogram;
				$kdgiat = $row->kdgiat;
				$kdoutput = $row->kdoutput;
				$kdlokasi = $row->kdlokasi;
				$kdkabkota = $row->kdkabkota;
				$kddekon = $row->kddekon;
				$kdsoutput = $row->kdsoutput;
				$kdkmpnen = $row->kdkmpnen;
				//db lokal
				//$kdskmpnen_ = $row->kdskmpnen;

				//db server
				$kdskmpnen_ = $row->kdskmpnen;
				$kdskmpnen = str_replace(' ', '', $kdskmpnen_);
				
				// $realisasi_fisik = 0;
				// $warning_icon = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_red.png\'>';

				//ngecek apakah input laporan sudah diisi atau belom
				$cek_paket = $this->lmm->cek_paket_by_idskmpnen($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen);
				//jika uda ngisi paket, bisa milih link lainnya
				if($cek_paket->num_rows > 0) {
					foreach ($cek_paket->result() as $row2) {
						$idpaket = $row2->idpaket;
					}
					//ini buat linknya coy
					$unggah ='<a href='.base_url().'index.php/e-monev/laporan_monitoring/daftar_dokumen/'.$idpaket.'><img border=\'0\' src=\''.base_url().'images/icon/upload2.png\'></a>';
					$masalah = '<a href='.base_url().'index.php/e-monev/laporan_monitoring/input_masalah/'.$idpaket.'><img border=\'0\' src=\''.base_url().'images/icon/lihat.png\'></a>';
					$grafik = '<a href='.base_url().'index.php/e-monev/laporan_monitoring/main_grafik/'.$idpaket.'><img border=\'0\' src=\''.base_url().'images/icon/grafik.png\'></a>';
				
					//ngecek progress fisik kontrak & swakelola dari idpaket
					if($this->lmm->get_progress_by_idpaket($idpaket)->num_rows() > 0)
					{
						//jika tahun anggaran sekarang > dari tahun pada saat login (session)
						if(date("Y") > $this->session->userdata('thn_anggaran')){ //jika tahun anggaran sudah lewat
							$realisasi_fisik_kontrak = $this->lmm->get_progress_by_idpaket_and_month($idpaket,11)->row()->realisasi_fisik_kontrak;
							$realisasi_fisik_swakelola = $this->lmm->get_progress_by_idpaket_and_month($idpaket,11)->row()->realisasi_fisik_swakelola;
						}else{ //jika kondisi salah
							if(date("m") == 1 ){ //realisasi fisik pada bulan 1 
								$realisasi_fisik_kontrak = $this->lmm->get_progress_by_year($idpaket,12,$this->session->userdata('thn_anggaran')-1)->row()->realisasi_fisik_kontrak;
								$realisasi_fisik_swakelola = $this->lmm->get_progress_by_year($idpaket,12,$this->session->userdata('thn_anggaran')-1)->row()->realisasi_fisik_swakelola;
							}else{ //realisasi fisik pada bulan x-1
								$realisasi_fisik_kontrak = $this->lmm->get_progress_by_idpaket_and_month($idpaket,date("m")-1)->row()->realisasi_fisik_kontrak;
								$realisasi_fisik_swakelola = $this->lmm->get_progress_by_idpaket_and_month($idpaket,date("m")-1)->row()->realisasi_fisik_swakelola;
							}
						}
					}
					else
					{
						$realisasi_fisik_kontrak = 0;
						$realisasi_fisik_swakelola = 0;
					}
					
					//warning icon realisasi fisik kontrak
					if($realisasi_fisik_kontrak < 50)
					{
						$warning_icon_kontrak = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_red.png\'>';
					}
					else if($realisasi_fisik_kontrak >= 50 && $realisasi_fisik_kontrak < 75)
					{
						$warning_icon_kontrak = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_yellow.png\'>';
					}
					else if($realisasi_fisik_kontrak >= 75 && $realisasi_fisik_kontrak <= 100)
					{
						$warning_icon_kontrak = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_green.png\'>';
					}
					else if($realisasi_fisik_kontrak > 100)
					{
						$warning_icon_kontrak = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_blue.png\'>';
					}

					//warning icon realisasi fisik swakelola
					if($realisasi_fisik_swakelola < 50)
					{
						$warning_icon_swakelola = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_red.png\'>';
					}
					else if($realisasi_fisik_swakelola >= 50 && $realisasi_fisik_swakelola < 75)
					{
						$warning_icon_swakelola = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_yellow.png\'>';
					}
					else if($realisasi_fisik_swakelola >= 75 && $realisasi_fisik_swakelola <= 100)
					{
						$warning_icon_swakelola = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_green.png\'>';
					}
					else if($realisasi_fisik_swakelola > 100)
					{
						$warning_icon_swakelola = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_blue.png\'>';
					}
				}
				//kalo belom ya ngisi laporan dulu euy!
				else  {
					$unggah = '<a href="#" onclick="alert(\'Anda harus mengisi Laporan terlebih dahulu\')"><img border=\'0\' src=\''.base_url().'images/icon/upload2.png\'></a>';
					$masalah = '<a href="#" onclick="alert(\'Anda harus mengisi Laporan terlebih dahulu\')"><img border=\'0\' src=\''.base_url().'images/icon/lihat.png\'></a>';
					$grafik = '<a href="#" onclick="alert(\'Anda harus mengisi Laporan terlebih dahulu\')"><img border=\'0\' src=\''.base_url().'images/icon/grafik.png\'></a>';

					//warning icon realisasi fisik kontrak
					if($realisasi_fisik_kontrak < 50)
					{
						$warning_icon_kontrak = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_red.png\'>';
					}
					else if($realisasi_fisik_kontrak >= 50 && $realisasi_fisik_kontrak < 75)
					{
						$warning_icon_kontrak = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_yellow.png\'>';
					}
					else if($realisasi_fisik_kontrak >= 75 && $realisasi_fisik_kontrak <= 100)
					{
						$warning_icon_kontrak = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_green.png\'>';
					}
					else if($realisasi_fisik_kontrak > 100)
					{
						$warning_icon_kontrak = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_blue.png\'>';
					}

					//warning icon realisasi fisik swakelola
					if($realisasi_fisik_swakelola < 50)
					{
						$warning_icon_swakelola = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_red.png\'>';
					}
					else if($realisasi_fisik_swakelola >= 50 && $realisasi_fisik_swakelola < 75)
					{
						$warning_icon_swakelola = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_yellow.png\'>';
					}
					else if($realisasi_fisik_swakelola >= 75 && $realisasi_fisik_swakelola <= 100)
					{
						$warning_icon_swakelola = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_green.png\'>';
					}
					else if($realisasi_fisik_swakelola > 100)
					{
						$warning_icon_swakelola = '<img border=\'0\' src=\''.base_url().'images/flexigrid/bulb_blue.png\'>';
					}
				}

				if($kd_role != Role_model::PEMBUAT_LAPORAN)
				{
					$record_items[] = array(
						$no,
						$no,
						'<div style=\'text-align:left\'>'.$row->nmsatker.'</div>',
						'<div style=\'text-align:left\'>'.$row->urkmpnen.'</div>',
						'<div style=\'text-align:left\'>'.$row->urskmpnen.'</div>',
						$realisasi_fisik_kontrak.' % '.$warning_icon_kontrak,
						$realisasi_fisik_swakelola.' % '.$warning_icon_swakelola,
						$masalah,
						'<a href='.base_url().'index.php/e-monev/laporan_monitoring/input_laporan/'.$thang.'/'.$kdjendok.'/'.$kdsatker.'/'.$kddept.'/'.$kdunit.'/'.$kdprogram.'/'.$kdgiat.'/'.$kdoutput.'/'.$kdlokasi.'/'.$kdkabkota.'/'.$kddekon.'/'.$kdsoutput.'/'.$kdkmpnen.'/'.$kdskmpnen.'><img border=\'0\' src=\''.base_url().'images/icon/input.png\'></a>',
						$grafik,			
						$unggah
					);
				}
				else
				{
					$record_items[] = array(
						$no,
						$no,
						'<div style=\'text-align:left\'>'.$row->urkmpnen.'</div>',
						'<div style=\'text-align:left\'>'.$row->urskmpnen.'</div>',
						$realisasi_fisik_kontrak.' % '.$warning_icon_kontrak,
						$realisasi_fisik_swakelola.' % '.$warning_icon_swakelola,
						$masalah,
						'<a href='.base_url().'index.php/e-monev/laporan_monitoring/input_laporan/'.$thang.'/'.$kdjendok.'/'.$kdsatker.'/'.$kddept.'/'.$kdunit.'/'.$kdprogram.'/'.$kdgiat.'/'.$kdoutput.'/'.$kdlokasi.'/'.$kdkabkota.'/'.$kddekon.'/'.$kdsoutput.'/'.$kdkmpnen.'/'.$kdskmpnen.'><img border=\'0\' src=\''.base_url().'images/icon/input.png\'></a>',
						$grafik,
						$unggah
					);
				}
				$realisasi_fisik_kontrak = 0;
				$realisasi_fisik_swakelola = 0;
		}
		if(isset($record_items))
			$this->output->set_output($this->flexigrid->json_build($records['record_count'],$record_items));
		else
			$this->output->set_output('{"page":"1","total":"0","rows":[]}');
	}
	
	function bulan()
	{
		$bulan = array(
					'1' => 'Januari',
					'2' => 'Februari',
					'3' => 'Maret',
					'4' => 'April',
					'5' => 'Mei',
					'6' => 'Juni',
					'7' => 'Juli',
					'8' => 'Agustus',
					'9' => 'September',
					'10' => 'Oktober',
					'11' => 'November',
					'12' => 'Desember'
					);
		return $bulan;
	}
	
	function input_masalah($d_skmpnen_id)
	{
		$data['d_skmpnen_id'] = $d_skmpnen_id;
		$data['content'] = $this->load->view('e-monev/main_permasalahan',$data,true);
		$this->load->view('main',$data);
	}
	
	function main_grafik($idpaket)
	{
		//$data['sub_komponen'] = $this->lmm->get_sub_komponen_by_id($d_skmpnen_id)->row()->urskmpnen;
		$data['idpaket'] = $idpaket;
		$data['content'] = $this->load->view('e-monev/main_graph',$data,true);
		$this->load->view('main',$data);
	}

	function grafik($idpaket)
	{
		$strXML = '';
		$strXML .= '<graph yAxisName=\'Presentase\' caption=\'Grafik Progress Fisik Pelaksanaan Paket\' subcaption=\'Tahun '.$this->session->userdata('thn_anggaran').'\' hovercapbg=\'FFECAA\' hovercapborder=\'F47E00\' formatNumberScale=\'0\' decimalPrecision=\'0\' showvalues=\'0\' numdivlines=\'5\' numVdivlines=\'0\' yaxisminvalue=\'1000\' yaxismaxvalue=\'100\'  rotateNames=\'1\' NumberSuffix=\'%25\'>
					<categories >
						<category name=\'Jan\' />
						<category name=\'Feb\' />
						<category name=\'Mar\' />
						<category name=\'Apr\' />
						<category name=\'Mei\' />
						<category name=\'Jun\' />
						<category name=\'Jul\' />
						<category name=\'Agt\' />
						<category name=\'Sep\' />
						<category name=\'Okt\' />
						<category name=\'Nop\' />
						<category name=\'Des\' />
					</categories>';
		//grafik data rencana fisik
		$strXML .= '<dataset seriesName=\'Rencana Kontraktual\' color=\'1D8BD1\' anchorBorderColor=\'1D8BD1\' anchorBgColor=\'1D8BD1\'>';
		foreach($this->lmm->get_rencana_by_idpaket($idpaket) as $row)
		{
			$strXML .= '<set value="'.$row->rencana_kontraktual.'" />';
		}
		$strXML .= '</dataset>';
		$strXML .= '<dataset seriesName=\'Rencana Swakelola\' color=\'F1683C\' anchorBorderColor=\'F1683C\' anchorBgColor=\'F1683C\'>';
		foreach($this->lmm->get_rencana_by_idpaket($idpaket) as $row)
		{
			$strXML .= '<set value="'.$row->rencana_swakelola.'" />';
		}
		$strXML .= '</dataset>';

		//grafik data progress fisik
		$strXML .= '<dataset seriesName=\'Progress Kontraktual\' color=\'9ae5f1\' anchorBorderColor=\'9ae5f1\' anchorBgColor=\'9ae5f1\'>';
		foreach($this->lmm->get_progress_by_idpaket($idpaket)->result() as $row)
		{
			$strXML .= '<set value="'.$row->progress_kontraktual.'" />';
		}
		$strXML .= '</dataset>';
		$strXML .= '<dataset seriesName=\'Progress Swakelola\' color=\'f1c23c\' anchorBorderColor=\'f1c23c\' anchorBgColor=\'f1c23c\'>';
		foreach($this->lmm->get_progress_by_idpaket($idpaket)->result() as $row)
		{
			$strXML .= '<set value="'.$row->progress_swakelola.'" />';
		}
		$strXML .= '</dataset>';
		$strXML .= '</graph>';
		$myFile = dirname(dirname(dirname(dirname(__FILE__)))).'/charts/testFile.xml';
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $strXML);
		fclose($fh);
		$graph = '<script type="text/javascript">
					   var chart = new FusionCharts("'.base_url().'charts/FCF_MSLine.swf", "ChartId", "600", "350");
					   chart.setDataURL("'.base_url().'charts/testFile.xml");		   
					   chart.render("chartdiv");
				  </script>';
		echo $graph;
	}

	//input laporan
	function input_laporan($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen)
	{
		$data['thang'] = $thang;
		$data['kdjendok'] = $kdjendok;
		$data['kdsatker'] = $kdsatker;
		$data['kddept'] = $kddept;
		$data['kdunit'] = $kdunit;
		$data['kdprogram'] = $kdprogram;
		$data['kdgiat'] = $kdgiat;
		$data['kdoutput'] = $kdoutput;
		$data['kdlokasi'] = $kdlokasi;
		$data['kdkabkota'] = $kdkabkota;
		$data['kddekon'] = $kddekon;
		$data['kdsoutput'] = $kdsoutput;
		$data['kdkmpnen'] = $kdkmpnen;
		$data['kdskmpnen'] = $kdskmpnen;

		$data['content'] = $this->load->view('e-monev/main_laporan',$data,true);
		$this->load->view('main',$data);
	}
	
	//form tampilan awal paket
	function form_paket($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen)
	{
		$data['nmsatker'] = $this->lmm->get_satker_by_idskmpnen($kdsatker);
		$data['nmprogram'] = $this->lmm->get_program_by_idskmpnen($kdprogram, $kddept, $kdunit);
		$data['nmgiat'] = $this->lmm->get_kegiatan_by_idskmpnen($kdprogram, $kddept, $kdunit, $kdgiat);
		$data['nmoutput'] = $this->lmm->get_output_by_idskmpnen($kdoutput, $kdgiat);
		$data['ursoutput'] = $this->lmm->get_soutput_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdjendok, $kddekon);
		$data['urkmpnen'] = $this->lmm->get_komponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdjendok, $kddekon);
		
		//cek subkomponen terdapat datanya atau tidak
		$cek_skomponen = $this->lmm->cek_skomponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdskmpnen, $kdjendok, $kddekon);
		if($cek_skomponen == TRUE) {
			$data['sub_komponen'] = $this->lmm->get_skomponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdskmpnen, $kdjendok, $kddekon);
		}
		else {
			$data['sub_komponen'] = '-';
		}
		
		//ngecek paket uda ada di table 'paket', jika sudah ada redirect detail paket
		if($this->lmm->get_paket_by_idskmpnen($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen)->num_rows > 0)
		{
			$this->load->view('e-monev/info_paket',$data);
		}
		//jika belum ada data, di masukkan ke table 'paket' terlebih dahulu
		else
		{
			$this->save_paket($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen);
			$this->load->view('e-monev/info_paket',$data);
		}
	}

	//simpan data ke table paket
	function save_paket($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen)
	{
		$data = array(
					'thang' => $thang,
					'kdjendok' => $kdjendok,
					'kdsatker' => $kdsatker,
					'kddept' => $kddept,
					'kdunit' => $kdunit,
					'kdprogram' => $kdprogram,
					'kdgiat' => $kdgiat,
					'kdoutput' => $kdoutput,
					'kdlokasi' => $kdlokasi,
					'kdkabkota' => $kdkabkota,
					'kddekon' => $kddekon,
					'kdsoutput' => $kdsoutput,
					'kdkmpnen' => $kdkmpnen,
					'kdskmpnen' => $kdskmpnen,
					);
		$this->lmm->add_paket_by_idskmpnen($data);
	}

	//grid menampilkan alokasi
	function daftar_alokasi($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen)
	{
		$alokasi = 0;
		$d_item = $this->lmm->get_d_item($thang,$kdjendok,$kdsatker,$kddept,$kdunit,$kdprogram,$kdgiat,$kdoutput,$kdlokasi,$kdkabkota,$kddekon,$kdsoutput,$kdkmpnen,$kdskmpnen);

		//ngecek paket
		$cek_paket = $this->lmm->get_paket_by_idskmpnen($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen);
		foreach ($cek_paket->result() as $row) {
			$idpaket = $row->idpaket;
		}

		$data['alokasi'] = 0;
		$data['alokasi'] = $this->lmm->get_sum_d_item($thang,$kdjendok,$kdsatker,$kddept,$kdunit,$kdprogram,$kdgiat,$kdoutput,$kdlokasi,$kdkabkota,$kddekon,$kdsoutput,$kdkmpnen,$kdskmpnen)->row()->jumlah;
		$data['d_item'] = $d_item;
		$data['option_jenis_item'] = $this->lmm->get_jenis_item();
		$data['idpaket'] = $idpaket;

		$this->load->view('e-monev/grid_alokasi',$data);
	}

	//fungsi memasukkan data jenis item per item
	function simpanitem($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen, $noitem, $kdjnsitem, $nilaikontrak, $alokasi, $no_item, $kdakun)
	{
		//ngecek paket
		$cek_paket = $this->lmm->get_paket_by_idskmpnen($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen, $kdskmpnen);

		if($cek_paket->num_rows > 0)
		{
			//id paket
			$idpaket = $cek_paket->row()->idpaket;

			//ambil alokasi per item
			//$jumlah_alokasi = $this->lmm->get_sum_per_item($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen, $noitem);
			
			//ngecek di jnsitem
			$get_jnsitem = $this->lmm->get_jnsitem_by_idpaket($idpaket,$no_item,$kdakun);
			//$alokasi = 0;

			if ($get_jnsitem->num_rows()) {
				if ($nilaikontrak > $alokasi) {
					//echo $jumlah_alokasi;
					$arr = array("result" => "false");
				}
				else {
					//jika uda ada data, di update ke tablenya
					$data = array(
						'idpaket' => $idpaket,
						'kdakun' => $kdakun,
						'noitem' => $no_item,
						'kdjnsitem' => $kdjnsitem,
						//'nilaikontraks' => $kdjnsitem == 1 ? $alokasi : $nilaikontrak
						'nilaikontrak' => $nilaikontrak
					);
					$this->lmm->update_jnsitem($data,$idpaket,$no_item,$kdakun);
					$arr = array("result" => "true");
				}
				
			}
			else {
				//jika baru insert baru, dimasukkan datanya
				$data = array(
					'idpaket' => $idpaket,
					'kdakun' => $kdakun,
					'noitem' => $no_item,
					'kdjnsitem' => $kdjnsitem,
					//'nilaikontrak' => $kdjnsitem == 1 ? 0 : $nilaikontrak
					'nilaikontrak' => $kdjnsitem == 1 ? $alokasi : $nilaikontrak
				);
				$this->lmm->add_kontrak_by_jenis_item($data);
				$arr = array("result" => "true");
			}
		}
		//$tes = array("result"=>"test");
		echo json_encode($arr);
	}

	//fungsi untuk mengambil jumlai alokasi per item
	function alokasi($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen, $noitem)
	{
		//ambil alokasi per item
		$jumlah_alokasi = $this->lmm->get_sum_per_item($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen, $noitem);
		echo $jumlah_alokasi;
	}
	
	//proses input rencana
	function input_rencana($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen)
	{
		$data['thang'] = $thang;
		$data['kdjendok'] = $kdjendok;
		$data['kdsatker'] = $kdsatker;
		$data['kddept'] = $kddept;
		$data['kdunit'] = $kdunit;
		$data['kdprogram'] = $kdprogram;
		$data['kdgiat'] = $kdgiat;
		$data['kdoutput'] = $kdoutput;
		$data['kdlokasi'] = $kdlokasi;
		$data['kdkabkota'] = $kdkabkota;
		$data['kddekon'] = $kddekon;
		$data['kdsoutput'] = $kdsoutput;
		$data['kdkmpnen'] = $kdkmpnen;
		$data['kdskmpnen'] = $kdskmpnen;

		$data['content'] = $this->load->view('e-monev/main_rencana',$data,true);
		$this->load->view('main',$data);
	}

	//tampilan awal table rencana
	function daftar_rencana($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen)
	{
		//ngecek paket dan table data jenis item
		$cek_paket = $this->lmm->get_paket_by_idskmpnen($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen);
		//id paket
		$idpaket = $cek_paket->row()->idpaket;
		//cek jenis item berdasarkan idpaket
		$jnsitem = $this->lmm->cek_jnsitem_by_idpaket($idpaket);
		
		//cek paket, jika ada lanjut
		if($cek_paket->num_rows > 0 && $jnsitem == TRUE)
		{
			//cek data di table dm_rencana_kontraktual
			$cek_rencana_kontrak = $this->lmm->cek_rencana_kontrak_by_idpaket($idpaket);

			//cek data di table dm_rencana_swakelola
			$cek_rencana_swakelola = $this->lmm->cek_rencana_swakelola_by_idpaket($idpaket);

			if ($cek_rencana_kontrak == FALSE && $cek_rencana_swakelola == FALSE) {
				foreach($this->bulan() as $key=>$value)
				{
					//masukkan data ke table dm_rencana_kontraktual
					$data = array(
						'idpaket' => $idpaket,
						'bulan' => $key,
						'rencana' => '0',
						'tahun' => $this->session->userdata('thn_anggaran')
						);
					$this->lmm->add_rencana_kontrak_by_idpaket($data);

					//masukkan data ke table dm_rencana_kontraktual
					$data2 = array(
						'idpaket' => $idpaket,
						'bulan' => $key,
						'rencana' => '0',
						'tahun' => $this->session->userdata('thn_anggaran')
						);
					$this->lmm->add_rencana_swakelola_by_idpaket($data2);
				}
			}

			$data['bulan'] = $this->bulan();
			$data['idpaket'] = $idpaket;
			$data['komponen'] = $this->lmm->get_komponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdjendok, $kddekon);
			
			//cek subkomponen terdapat datanya atau tidak
			$cek_skomponen = $this->lmm->cek_skomponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdskmpnen, $kdjendok, $kddekon);
			if($cek_skomponen == TRUE) {
				$data['sub_komponen'] = $this->lmm->get_skomponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdskmpnen, $kdjendok, $kddekon);
			}
			else {
				$data['sub_komponen'] = '-';
			}
			$data['daftar_rencana'] = $this->lmm->get_rencana_by_idpaket($idpaket);
			$data['dana_kontrak'] = $this->lmm->get_kontrak_by_idpaket($idpaket)->row()->nilaikontrak;
			$data['dana_swakelola'] = $this->lmm->get_swakelola_by_idpaket($idpaket)->row()->nilaikontrak;
			//$data['dana_swakelola'] = $this->lmm->get_jumlah_swakelola($thang,$kdjendok,$kdsatker,$kddept,$kdunit,$kdprogram,$kdgiat,$kdoutput,$kdlokasi,$kdkabkota,$kddekon,$kdsoutput,$kdkmpnen,$kdskmpnen,$idpaket);
			$this->load->view('e-monev/grid_rencana',$data);
		}
		else
		{
			echo 'Data alokasi per item harus diisi terlebih dahulu.';
		}
	}

	//form input rencana kontraktual
	function form_input_rencana_kontrak($rencana_id, $idpaket, $bulan)
	{
		$result = $this->lmm->get_rencana_kontrak_by_id($rencana_id)->row();
		$array_bulan = $this->bulan();
		$data['rencana_kontrak'] = $result->rencana;
		if($bulan > 1){
			$data['rencana_kontrak_sebelum'] = $this->lmm->get_rencana_kontrak_by_id($rencana_id-1)->row()->rencana;
		}else{
			$data['rencana_kontrak_sebelum'] = 0;
		}
		$data['rencana_id'] = $rencana_id;
		$data['idpaket'] = $idpaket;
		$data['bulan'] = $array_bulan[$bulan];
		
		$this->load->view('e-monev/form_input_rencana_kontrak',$data);
	}

	//simpan data rencana kontraktual ke dalam database
	function save_rencana_kontrak($rencana_id, $idpaket)
	{
		$arr = '';
		$cek = null;
		$rencana_kontrak = $this->input->post('rencana_kontrak');
		$data = array(
			'rencana'=> $rencana_kontrak
		);

		$tgl_tengah = $this->lmm->get_referensi_by_id(1)->row()->tanggal;
		$tahun = $this->session->userdata('thn_anggaran');

		foreach ($this->lmm->get_rencana_kontrak_after_rencana_id($rencana_id,$idpaket) as $row)
		{
			//update tabel rencana kontraktual
			$this->lmm->update_rencana_kontrak($row->rencana_id,$data);
			$bulan = $row->bulan;
			$idpaket = $row->idpaket;
			if ($this->lmm->cek_progress_kontraktual_by_idpaket($idpaket) == TRUE)
			{
				//jika rencana yang diubah bulan ke-1
				if ($bulan == 1)
				{
					$data_progress = $this->lmm->get_progress_kontrak_per_bulan($idpaket, $bulan);
					$batasan_tanggal = $tahun.'-'.($bulan+1).'-'.$tgl_tengah; //batas tanggal pengisian progres
					if($data_progress->tanggal != null && $data_progress->tanggal != '' && $data_progress->tanggal <= $batasan_tanggal)
					{
						if($rencana_kontrak == 0){
							$realisasi_fisik = 0;
						}else{
							$realisasi_fisik = round($data_progress->progress / $rencana_kontrak * 100,2);
						}
						$real = array(
							'realisasi_fisik' 	=> $realisasi_fisik
						);
						//update realisasi fisik ke database
						$this->lmm->update_progress_kontrak($data_progress->progress_id, $real);
					}
				}
				else
				{
					//update progres bulan x-1
					$data_progress1 = $this->lmm->get_progress_kontrak_per_bulan($idpaket,($bulan-1));
					$batasan_tanggal1 = $tahun.'-'.$bulan.'-'.$tgl_tengah; //batas tanggal pengisian progres
					if($data_progress1->tanggal != null && $data_progress1->tanggal != '' && $data_progress1->tanggal > $batasan_tanggal1){
						if($rencana_kontrak == 0){
							$realisasi_fisik = 0;
						}else{
							$realisasi_fisik = round($data_progress1->progress / $rencana_kontrak * 100,2);
						}
						$real = array(
							'realisasi_fisik' 	=> $realisasi_fisik
						);
						$this->lmm->update_progress_kontrak($data_progress1->progress_id, $real);
					}

					//update progres bulan x
					$data_progress2 = $this->lmm->get_progress_kontrak_per_bulan($idpaket,$bulan);
					$batasan_tanggal2 = $tahun.'-'.($bulan+1).'-'.$tgl_tengah; //batas tanggal pengisian progres
					if($bulan == 12){
						if($rencana_kontrak == 0){
							$realisasi_fisik = 0;
						}else{
							$realisasi_fisik = round($data_progress2->progress / $rencana_kontrak * 100,2);
						}
						$real = array(
							'realisasi_fisik' 	=> $realisasi_fisik
						);
						$this->lmm->update_progress_kontrak($data_progress2->progress_id, $real);
					}else{
						if($data_progress2->tanggal != null && $data_progress2->tanggal != '' && $data_progress2->tanggal <= $batasan_tanggal2){
							if($rencana_kontrak == 0){
								$realisasi_fisik = 0;
							}else{
								$realisasi_fisik = round($data_progress2->progress / $rencana_kontrak * 100,2);
							}
							$real = array(
								'realisasi_fisik' 	=> $realisasi_fisik
							);
							$this->lmm->update_progress_kontrak($data_progress2->progress_id, $real);
						}
					}
				}
			}
		}

		$arr = array("result" => "true");

		echo json_encode($arr);
	}

	//form input rencana kontraktual
	function form_input_rencana_swakelola($rencana_id, $idpaket, $bulan)
	{
		$result = $this->lmm->get_rencana_swakelola_by_id($rencana_id)->row();
		$array_bulan = $this->bulan();
		$data['rencana_swakelola'] = $result->rencana;
		if($bulan > 1){
			$data['rencana_swakelola_sebelum'] = $this->lmm->get_rencana_swakelola_by_id($rencana_id-1)->row()->rencana;
		}else{
			$data['rencana_swakelola_sebelum'] = 0;
		}
		$data['rencana_id'] = $rencana_id;
		$data['idpaket'] = $idpaket;
		$data['bulan'] = $array_bulan[$bulan];

		$this->load->view('e-monev/form_input_rencana_swakelola',$data);
	}

	//simpan data rencana swakelola ke dalam database
	function save_rencana_swakelola($rencana_id, $idpaket)
	{
		$arr = '';
		$cek = null;
		$rencana_swakelola = $this->input->post('rencana_swakelola');
		$data = array(
				'rencana'=> $rencana_swakelola
					);

		$tgl_tengah = $this->lmm->get_referensi_by_id(1)->row()->tanggal;
		$tahun = $this->session->userdata('thn_anggaran');

		foreach ($this->lmm->get_rencana_swakelola_after_rencana_id($rencana_id,$idpaket) as $row)
		{
			//update tabel rencana kontraktual
			$this->lmm->update_rencana_swakelola($row->rencana_id,$data);
			$bulan = $row->bulan;
			$idpaket = $row->idpaket;
			if ($this->lmm->cek_progress_swakelola_by_idpaket($idpaket) == TRUE)
			{
				//jika rencana yang diubah bulan ke-1
				if ($bulan == 1)
				{
					$data_progress = $this->lmm->get_progress_swakelola_per_bulan($idpaket, $bulan);
					$batasan_tanggal = $tahun.'-'.($bulan+1).'-'.$tgl_tengah; //batas tanggal pengisian progres
					if($data_progress->tanggal != null && $data_progress->tanggal != '' && $data_progress->tanggal <= $batasan_tanggal)
					{
						if($rencana_swakelola == 0){
							$realisasi_fisik = 0;
						}else{
							$realisasi_fisik = round($data_progress->progress / $rencana_swakelola * 100,2);
						}
						$real = array(
							'realisasi_fisik' 	=> $realisasi_fisik
						);
						//update realisasi fisik ke database
						$this->lmm->update_progress_swakelola($data_progress->progress_id, $real);
					}
				}
				else
				{
					//update progres bulan x-1
					$data_progress1 = $this->lmm->get_progress_swakelola_per_bulan($idpaket,($bulan-1));
					$batasan_tanggal1 = $tahun.'-'.$bulan.'-'.$tgl_tengah; //batas tanggal pengisian progres
					if($data_progress1->tanggal != null && $data_progress1->tanggal != '' && $data_progress1->tanggal > $batasan_tanggal1){
						if($rencana_swakelola == 0){
							$realisasi_fisik = 0;
						}else{
							$realisasi_fisik = round($data_progress1->progress / $rencana_swakelola * 100,2);
						}
						$real = array(
							'realisasi_fisik' 	=> $realisasi_fisik
						);
						$this->lmm->update_progress_swakelola($data_progress1->progress_id, $real);
					}

					//update progres bulan x
					$data_progress2 = $this->lmm->get_progress_swakelola_per_bulan($idpaket,$bulan);
					$batasan_tanggal2 = $tahun.'-'.($bulan+1).'-'.$tgl_tengah; //batas tanggal pengisian progres
					if($bulan == 12){
						if($rencana_swakelola == 0){
							$realisasi_fisik = 0;
						}else{
							$realisasi_fisik = round($data_progress2->progress / $rencana_swakelola * 100,2);
						}
						$real = array(
							'realisasi_fisik' 	=> $realisasi_fisik
						);
						$this->lmm->update_progress_swakelola($data_progress2->progress_id, $real);
					}else{
						if($data_progress2->tanggal != null && $data_progress2->tanggal != '' && $data_progress2->tanggal <= $batasan_tanggal2){
							if($rencana_swakelola == 0){
								$realisasi_fisik = 0;
							}else{
								$realisasi_fisik = round($data_progress2->progress / $rencana_swakelola * 100,2);
							}
							$real = array(
								'realisasi_fisik' 	=> $realisasi_fisik
							);
							$this->lmm->update_progress_swakelola($data_progress2->progress_id, $real);
						}
					}
				}
			}
		}

		$arr = array("result" => "true");
		
		echo json_encode($arr);
	}

	//proses input progress swakelola dan kontraktual
	function input_progress($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen)
	{
		$data['thang'] = $thang;
		$data['kdjendok'] = $kdjendok;
		$data['kdsatker'] = $kdsatker;
		$data['kddept'] = $kddept;
		$data['kdunit'] = $kdunit;
		$data['kdprogram'] = $kdprogram;
		$data['kdgiat'] = $kdgiat;
		$data['kdoutput'] = $kdoutput;
		$data['kdlokasi'] = $kdlokasi;
		$data['kdkabkota'] = $kdkabkota;
		$data['kddekon'] = $kddekon;
		$data['kdsoutput'] = $kdsoutput;
		$data['kdkmpnen'] = $kdkmpnen;
		$data['kdskmpnen'] = $kdskmpnen;

		$data['content'] = $this->load->view('e-monev/main_progress',$data,true);
		$this->load->view('main',$data);
	}

	function input_progress_swa($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen)
	{
		$data['thang'] = $thang;
		$data['kdjendok'] = $kdjendok;
		$data['kdsatker'] = $kdsatker;
		$data['kddept'] = $kddept;
		$data['kdunit'] = $kdunit;
		$data['kdprogram'] = $kdprogram;
		$data['kdgiat'] = $kdgiat;
		$data['kdoutput'] = $kdoutput;
		$data['kdlokasi'] = $kdlokasi;
		$data['kdkabkota'] = $kdkabkota;
		$data['kddekon'] = $kddekon;
		$data['kdsoutput'] = $kdsoutput;
		$data['kdkmpnen'] = $kdkmpnen;
		$data['kdskmpnen'] = $kdskmpnen;

		$data['content'] = $this->load->view('e-monev/main_progress2',$data,true);
		$this->load->view('main',$data);
	}

	//proses tampilan awal table progres kontraktual
	function daftar_progress_kontraktual($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen)
	{
		//ngecek paket
		$cek_paket = $this->lmm->get_paket_by_idskmpnen($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen);
		//id paket
		$idpaket = $cek_paket->row()->idpaket;

		if($cek_paket->num_rows() > 0 && $this->lmm->cek_rencana_kontrak_terisi_by_idpaket($idpaket) == TRUE)
		{
			if($this->lmm->cek_progress_kontraktual_by_idpaket($idpaket) == FALSE && $this->lmm->cek_progress_swakelola_by_idpaket($idpaket) == FALSE) {
				foreach($this->bulan() as $key=>$value)
				{
					//masukkan data ke table dm_progress_kontraktual
					$data = array(
						'idpaket' => $idpaket,
						'bulan' => $key,
						'tahun' => $this->session->userdata('thn_anggaran'),
						'progress' => '0',
						);
					$this->lmm->add_progress_kontrak_by_idpaket($data);

					//masukkan data ke table dm_progress_swakelola
					$data2 = array(
						'idpaket' => $idpaket,
						'bulan' => $key,
						'tahun' => $this->session->userdata('thn_anggaran'),
						'progress' => '0',
						);
					$this->lmm->add_progress_swakelola_by_idpaket($data2);
				}
			}

			//proses ambil data progress kontraktual & swakelola
			//$data['data_progress'] = $this->lmm->get_progress_by_idpaket($idpaket);

			//proses ambil data progres per kontraktual (dm_progress_kontraktual)
			$data['bulan'] = $this->bulan();
			$data['idpaket'] = $idpaket;
			$data['daftar_progress'] = $this->lmm->get_prog_renc_kontrak_by_idpaket($idpaket);
			$data['komponen'] = $this->lmm->get_komponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdjendok, $kddekon);
			//cek subkomponen terdapat datanya atau tidak
			$cek_skomponen = $this->lmm->cek_skomponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdskmpnen, $kdjendok, $kddekon);
			if($cek_skomponen == TRUE) {
				$data['sub_komponen'] = $this->lmm->get_skomponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdskmpnen, $kdjendok, $kddekon);
			}
			else {
				$data['sub_komponen'] = '-';
			}
			$this->load->view('e-monev/grid_progress_kontraktual',$data);
		}
		else
		{
			echo 'Data alokasi dan data rencana fisik kontraktual harus diisi terlebih dahulu.';
		}
	}

	//proses tampilan awal table progres daftar_progress_swakelola
	function daftar_progress_swakelola($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen)
	{
		//ngecek paket
		$cek_paket = $this->lmm->get_paket_by_idskmpnen($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen);
		//id paket
		$idpaket = $cek_paket->row()->idpaket;

		if($cek_paket->num_rows() > 0 && $this->lmm->cek_rencana_swakelola_terisi_by_idpaket($idpaket) == TRUE)
		{
			//proses ambil data progress kontraktual & swakelola
			//$data['data_progress'] = $this->lmm->get_progress_by_idpaket($idpaket);

			//proses ambil data progres per kontraktual (dm_progress_kontraktual)
			$data['bulan'] = $this->bulan();
			$data['idpaket'] = $idpaket;
			$data['daftar_progress'] = $this->lmm->get_prog_renc_swakelola_by_idpaket($idpaket);
			$data['komponen'] = $this->lmm->get_komponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdjendok, $kddekon);
			//cek subkomponen terdapat datanya atau tidak
			$cek_skomponen = $this->lmm->cek_skomponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdskmpnen, $kdjendok, $kddekon);
			if($cek_skomponen == TRUE) {
				$data['sub_komponen'] = $this->lmm->get_skomponen_by_idskmpnen($thang, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kdsoutput, $kdkmpnen, $kdskmpnen, $kdjendok, $kddekon);
			}
			else {
				$data['sub_komponen'] = '-';
			}
			$this->load->view('e-monev/grid_progress_swakelola',$data);
		}
		else
		{
			echo 'Data alokasi dan data rencana fisik swakelola harus diisi terlebih dahulu.';
		}
	}

	//ngecek rencana kontraktual ada isinya atau belum
	function cek_rencana_kontraktual($idpaket, $bulan)
	{
		$cek_rencana = $this->lmm->get_rencana_kontrak_per_bulan($idpaket,$bulan);
		if($cek_rencana == 0){
			$arr = array("result" => "rencana_0");
		}else{
			$arr = array("result" => "true");
		}
		echo json_encode($arr);
	}

	//ngecek rencana swakelola ada isinya atau belum
	function cek_rencana_swakelola($idpaket, $bulan)
	{
		$cek_rencana = $this->lmm->get_rencana_swakelola_per_bulan($idpaket,$bulan);
		if($cek_rencana == 0){
			$arr = array("result" => "rencana_0");
		}else{
			$arr = array("result" => "true");
		}
		echo json_encode($arr);
	}

	//form input progress swakelola
	function form_input_progress_swakelola($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen, $progress_id, $idpaket, $bulan)
	{
		$result = $this->lmm->get_progress_swakelola_by_id($progress_id)->row();
		$array_bulan = $this->bulan();
		
		$data['thang'] = $thang;
		$data['kdjendok'] = $kdjendok;
		$data['kdsatker'] = $kdsatker;
		$data['kddept'] = $kddept;
		$data['kdunit'] = $kdunit;
		$data['kdprogram'] = $kdprogram;
		$data['kdgiat'] = $kdgiat;
		$data['kdoutput'] = $kdoutput;
		$data['kdlokasi'] = $kdlokasi;
		$data['kdkabkota'] = $kdkabkota;
		$data['kddekon'] = $kddekon;
		$data['kdsoutput'] = $kdsoutput;
		$data['kdkmpnen'] = $kdkmpnen;
		$data['kdskmpnen'] = $kdskmpnen;

		$data['progress'] = $result->progress; 
		$data['progress_id'] = $progress_id;
		$data['idpaket'] = $idpaket;
		$data['bulan'] = $array_bulan[$bulan];
		$this->load->view('e-monev/form_input_progress_swakelola',$data);
	}

	//form input progress kontraktual
	function form_input_progress_kontraktual($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen, $progress_id, $idpaket, $bulan)
	{
		$result = $this->lmm->get_progress_kontrak_by_id($progress_id)->row();
		$array_bulan = $this->bulan();

		$data['thang'] = $thang;
		$data['kdjendok'] = $kdjendok;
		$data['kdsatker'] = $kdsatker;
		$data['kddept'] = $kddept;
		$data['kdunit'] = $kdunit;
		$data['kdprogram'] = $kdprogram;
		$data['kdgiat'] = $kdgiat;
		$data['kdoutput'] = $kdoutput;
		$data['kdlokasi'] = $kdlokasi;
		$data['kdkabkota'] = $kdkabkota;
		$data['kddekon'] = $kddekon;
		$data['kdsoutput'] = $kdsoutput;
		$data['kdkmpnen'] = $kdkmpnen;
		$data['kdskmpnen'] = $kdskmpnen;

		$data['progress'] = $result->progress; 
		$data['progress_id'] = $progress_id;
		$data['idpaket'] = $idpaket;
		$data['bulan'] = $array_bulan[$bulan];
		
		$this->load->view('e-monev/form_input_progress_kontrak',$data);
	}

	//fungsi untuk menyimpan data progress fisik swakelola
	function save_progress_swakelola($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen, $progress_id, $idpaket)
	{
		$bulan_ini = $this->lmm->get_progress_swakelola_by_id($progress_id)->row()->bulan;
		$tahun = $this->session->userdata('thn_anggaran');
		$tgl_atas = $this->lmm->get_referensi_by_id(2)->row()->tanggal;
		$tgl_tengah = $this->lmm->get_referensi_by_id(1)->row()->tanggal;
		$tgl_bawah = $this->lmm->get_referensi_by_id(3)->row()->tanggal;
		$batasan_tanggal = $tahun.'-'.($bulan_ini+1).'-'.$tgl_tengah; //batas tanggal pengisian progres
		$config['upload_path'] = './file/';
		$config['allowed_types'] = 'doc|docx|pdf|txt|jpg|jpeg';
		$config['max_size']  = '10240';

		$this->load->library('upload', $config);
		
		// create directory if doesn't exist
		if(!is_dir($config['upload_path']))	mkdir($config['upload_path'], 0777);
		
		$file='';
		if(!empty($_FILES['file']['name']))
		{	
			if($this->lmm->is_exist_progres_swa_more_than_100_bef($progress_id, $idpaket) == TRUE)
			{
				$this->session->set_flashdata('error_progres', '<div align="center" class="errorbox">Progres pada bulan sebelumnya telah mencapai 100 %</div>');
			}
			else
			{
				$upload = $this->upload->do_upload('file');
				$data = $this->upload->data('file');
				if($data['file_size'] > 0) $file = $data['file_name'];
				$data_file = array(
					'tanggal' 	=> date('Y-m-d'),
					'progress' 	=> $this->input->post('progress'),
					'dokumen' 	=> $file
				);

				foreach($this->lmm->get_progress_swakelola_after_progress_id($progress_id, $idpaket) as $row)
				{
					//update progres dan upload data ke database
					$this->lmm->update_progress_swakelola($progress_id,$data_file);
					//melakukan penghitungan untuk realisasi fisik progres swakelola
					$batas_tanggal = $tahun.'-'.($row->bulan+1).'-'.$tgl_tengah;
					//cek tanggal sekarang apakah lebih dari batas tanggal yg telah ditentukan
					if(date('Y-m-d') > $batas_tanggal){
						//jika iya, rencana fisik diambil dari satu bulan setelah sekarang
						$rencana_fisik = $this->lmm->get_rencana_swakelola_per_bulan($idpaket,$row->bulan+1);
					}else{
						//jika tidak, rencana fisik diambil bulan sekarang
						$rencana_fisik = $this->lmm->get_rencana_swakelola_per_bulan($idpaket,$row->bulan);
					}
					//jika rencana fisik nya 0, realisasi fisik nya juga 0
					if($rencana_fisik == 0){
						$realisasi_fisik = 0;
					}
					//jika ada nilainya, maka rumusnya dibawah ini coy
					else{
						$realisasi_fisik = round($this->input->post('progress') / $rencana_fisik * 100,2);
					}				
					$data = array(
							'realisasi_fisik' 	=> $realisasi_fisik
					);
					//update realisasi fisik ke database
					$this->lmm->update_progress_swakelola($row->progress_id,$data);
				}
			}
		}
		else
		{
			if($this->lmm->is_exist_progres_kont_more_than_100_bef($progress_id, $idpaket) == TRUE)
			{
				$this->session->set_flashdata('error_progres', '<div align="center" class="errorbox">Progres pada bulan sebelumnya telah mencapai 100 %</div>');
			}
			else
			{
				$data_file = array(
				'tanggal' 	=> date('Y-m-d'),
				'progress' 	=> $this->input->post('progress')
				);

				foreach($this->lmm->get_progress_swakelola_after_progress_id($progress_id, $idpaket) as $row)
				{
					//update progres dan upload data ke database
					$this->lmm->update_progress_swakelola($row->progress_id,$data_file);
					//melakukan penghitungan untuk realisasi fisik progres swakelola
					$batas_tanggal = $tahun.'-'.($row->bulan+1).'-'.$tgl_tengah;
					//cek tanggal sekarang apakah lebih dari batas tanggal yg telah ditentukan
					if(date('Y-m-d') > $batas_tanggal){
						//jika iya, rencana fisik diambil dari satu bulan setelah sekarang
						$rencana_fisik = $this->lmm->get_rencana_swakelola_per_bulan($idpaket,$row->bulan+1);
					}else{
						//jika tidak, rencana fisik diambil bulan sekarang
						$rencana_fisik = $this->lmm->get_rencana_swakelola_per_bulan($idpaket,$row->bulan);
					}
					//jika rencana fisik nya 0, realisasi fisik nya juga 0
					if($rencana_fisik == 0){
						$realisasi_fisik = 0;
					}
					//jika ada nilainya, maka rumusnya dibawah ini coy
					else{
						$realisasi_fisik = round($this->input->post('progress') / $rencana_fisik * 100,2);
					}				
					$data = array(
							'realisasi_fisik' 	=> $realisasi_fisik
					);
					//update realisasi fisik ke database
					$this->lmm->update_progress_swakelola($row->progress_id,$data);
				}
			}
		}
		redirect('e-monev/laporan_monitoring/input_progress_swa/'.$thang.'/'.$kdjendok.'/'.$kdsatker.'/'.$kddept.'/'.$kdunit.'/'.$kdprogram.'/'.$kdgiat.'/'.$kdoutput.'/'.$kdlokasi.'/'.$kdkabkota.'/'.$kddekon.'/'.$kdsoutput.'/'.$kdkmpnen.'/'.$kdskmpnen);
	}

	//fungsi untuk menyimpan data progress fisik kontraktual
	function save_progress_kontrak($thang, $kdjendok, $kdsatker, $kddept, $kdunit, $kdprogram, $kdgiat, $kdoutput, $kdlokasi, $kdkabkota, $kddekon, $kdsoutput, $kdkmpnen, $kdskmpnen, $progress_id, $idpaket)
	{
		$bulan_ini = $this->lmm->get_progress_kontrak_by_id($progress_id)->row()->bulan;
		$tahun = $this->session->userdata('thn_anggaran');
		$tgl_atas = $this->lmm->get_referensi_by_id(2)->row()->tanggal;
		$tgl_tengah = $this->lmm->get_referensi_by_id(1)->row()->tanggal;
		$tgl_bawah = $this->lmm->get_referensi_by_id(3)->row()->tanggal;
		$batasan_tanggal = $tahun.'-'.($bulan_ini+1).'-'.$tgl_tengah; //batas tanggal pengisian progres
		$config['upload_path'] = './file/';
		$config['allowed_types'] = 'doc|docx|pdf|txt|jpg|jpeg';
		$config['max_size']  = '10240';

		$this->load->library('upload', $config);
		
		// create directory if doesn't exist
		if(!is_dir($config['upload_path']))	mkdir($config['upload_path'], 0777);
		
		$file='';
		if(!empty($_FILES['file']['name']))
		{	
			if($this->lmm->is_exist_progres_kont_more_than_100_bef($progress_id, $idpaket) == TRUE)
			{
				$this->session->set_flashdata('error_progres', '<div align="center" class="errorbox">Progres pada bulan sebelumnya telah mencapai 100 %</div>');
			}
			else
			{
				$upload = $this->upload->do_upload('file');
				$data = $this->upload->data('file');
				if($data['file_size'] > 0) $file = $data['file_name'];
				$data_file = array(
					'tanggal' 	=> date('Y-m-d'),
					'progress' 	=> $this->input->post('progress'),
					'dokumen' 	=> $file
				);

				foreach($this->lmm->get_progress_kontrak_after_progress_id($progress_id, $idpaket) as $row)
				{
					//update progres dan upload data ke database
					$this->lmm->update_progress_kontrak($progress_id,$data_file);
					//melakukan penghitungan untuk realisasi fisik progres swakelola
					$batas_tanggal = $tahun.'-'.($row->bulan+1).'-'.$tgl_tengah;
					//cek tanggal sekarang apakah lebih dari batas tanggal yg telah ditentukan
					if(date('Y-m-d') > $batas_tanggal){
						//jika iya, rencana fisik diambil dari satu bulan setelah sekarang
						$rencana_fisik = $this->lmm->get_rencana_kontrak_per_bulan($idpaket,$row->bulan+1);
					}else{
						//jika tidak, rencana fisik diambil bulan sekarang
						$rencana_fisik = $this->lmm->get_rencana_kontrak_per_bulan($idpaket,$row->bulan);
					}
					//jika rencana fisik nya 0, realisasi fisik nya juga 0
					if($rencana_fisik == 0){
						$realisasi_fisik = 0;
					}
					//jika ada nilainya, maka rumusnya dibawah ini coy
					else{
						$realisasi_fisik = round($this->input->post('progress') / $rencana_fisik * 100,2);
					}				
					$data = array(
							'realisasi_fisik' 	=> $realisasi_fisik
					);
					//update realisasi fisik ke database
					$this->lmm->update_progress_kontrak($row->progress_id,$data);
				}
			}
		}
		else
		{
			if($this->lmm->is_exist_progres_kont_more_than_100_bef($progress_id, $idpaket) == TRUE)
			{
				$this->session->set_flashdata('error_progres', '<div align="center" class="errorbox">Progres pada bulan sebelumnya telah mencapai 100 %</div>');
			}
			else
			{
				$data_file = array(
				'tanggal' 	=> date('Y-m-d'),
				'progress' 	=> $this->input->post('progress')
				);

				foreach($this->lmm->get_progress_kontrak_after_progress_id($progress_id, $idpaket) as $row)
				{
					//update progres dan upload data ke database
					$this->lmm->update_progress_kontrak($row->progress_id,$data_file);
					//melakukan penghitungan untuk realisasi fisik progres swakelola
					$batas_tanggal = $tahun.'-'.($row->bulan+1).'-'.$tgl_tengah;
					//cek tanggal sekarang apakah lebih dari batas tanggal yg telah ditentukan
					if(date('Y-m-d') > $batas_tanggal){
						//jika iya, rencana fisik diambil dari satu bulan setelah sekarang
						$rencana_fisik = $this->lmm->get_rencana_kontrak_per_bulan($idpaket,$row->bulan+1);
					}else{
						//jika tidak, rencana fisik diambil bulan sekarang
						$rencana_fisik = $this->lmm->get_rencana_kontrak_per_bulan($idpaket,$row->bulan);
					}
					//jika rencana fisik nya 0, realisasi fisik nya juga 0
					if($rencana_fisik == 0){
						$realisasi_fisik = 0;
					}
					//jika ada nilainya, maka rumusnya dibawah ini coy
					else{
						$realisasi_fisik = round($this->input->post('progress') / $rencana_fisik * 100,2);
					}				
					$data = array(
							'realisasi_fisik' 	=> $realisasi_fisik
					);
					//update realisasi fisik ke database
					$this->lmm->update_progress_kontrak($row->progress_id,$data);
				}
			}
		}
		
		redirect('e-monev/laporan_monitoring/input_progress/'.$thang.'/'.$kdjendok.'/'.$kdsatker.'/'.$kddept.'/'.$kdunit.'/'.$kdprogram.'/'.$kdgiat.'/'.$kdoutput.'/'.$kdlokasi.'/'.$kdkabkota.'/'.$kddekon.'/'.$kdsoutput.'/'.$kdkmpnen.'/'.$kdskmpnen);
	}

	// fungsi untuk download file pada progress kontraktual yg ter-upload
	function download_file_kontrak($progress_id)
	{
		// gett the file from DB
		$this->load->helper('download');
		$record = $this->lmm->get_progress_kontrak_by_id($progress_id);
		if($record->num_rows() > 0 )
		{
			$nama_file = $record->row()->dokumen;
			if (is_file('./file/'.$nama_file))
			{
				$data = file_get_contents('./file/'.$nama_file);			
				force_download($nama_file, $data); 
			}
		}
		else
		{
			echo 'File tidak ditemukan!';
		}
	}
	// fungsi untuk download file pada progress swakelola yg ter-upload
	function download_file_swakelola($progress_id)
	{
		// gett the file from DB
		$this->load->helper('download');
		$record = $this->lmm->get_progress_swakelola_by_id($progress_id);
		if($record->num_rows() > 0 )
		{
			$nama_file = $record->row()->dokumen;
			if (is_file('./file/'.$nama_file))
			{
				$data = file_get_contents('./file/'.$nama_file);			
				force_download($nama_file, $data); 
			}
		}
		else
		{
			echo 'File tidak ditemukan!';
		}
	}

	//grafik rencana fisik
	function grafik_rencana($idpaket)
	{
		$strXML = '';
		$strXML .= '<graph yAxisName=\'Presentase\' caption=\'Grafik Rencana Fisik Pelaksanaan Paket\' subcaption=\'Tahun '.$this->session->userdata('thn_anggaran').'\' hovercapbg=\'FFECAA\' hovercapborder=\'F47E00\' formatNumberScale=\'0\' decimalPrecision=\'0\' showvalues=\'0\' numdivlines=\'5\' numVdivlines=\'0\' yaxisminvalue=\'1000\' yaxismaxvalue=\'100\'  rotateNames=\'1\' NumberSuffix=\'%25\'>
					<categories >
						<category name=\'Jan\' />
						<category name=\'Feb\' />
						<category name=\'Mar\' />
						<category name=\'Apr\' />
						<category name=\'Mei\' />
						<category name=\'Jun\' />
						<category name=\'Jul\' />
						<category name=\'Agt\' />
						<category name=\'Sep\' />
						<category name=\'Okt\' />
						<category name=\'Nop\' />
						<category name=\'Des\' />
					</categories>';
		$strXML .= '<dataset seriesName=\'Rencana Kontraktual\' color=\'1D8BD1\' anchorBorderColor=\'1D8BD1\' anchorBgColor=\'1D8BD1\'>';
		foreach($this->lmm->get_rencana_by_idpaket($idpaket) as $row)
		{
			$strXML .= '<set value="'.$row->rencana_kontraktual.'" />';
		}
		$strXML .= '</dataset>';
		$strXML .= '<dataset seriesName=\'Rencana Swakelola\' color=\'F1683C\' anchorBorderColor=\'F1683C\' anchorBgColor=\'F1683C\'>';
		foreach($this->lmm->get_rencana_by_idpaket($idpaket) as $row)
		{
			$strXML .= '<set value="'.$row->rencana_swakelola.'" />';
		}
		$strXML .= '</dataset>';
		$strXML .= '</graph>';
		$myFile = dirname(dirname(dirname(dirname(__FILE__)))).'/charts/testFile.xml';
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $strXML);
		fclose($fh);
		$graph = '<script type="text/javascript">
					   var chart = new FusionCharts("'.base_url().'charts/FCF_MSLine.swf", "ChartId", "600", "350");
					   chart.setDataURL("'.base_url().'charts/testFile.xml");		   
					   chart.render("chartdiv");
				  </script>';
		$data['graph'] = $graph;
		$data['idpaket'] = $idpaket;
		$this->load->view('e-monev/grafik_rencana', $data);
	}

	//grafik progress fisik
	function grafik_progress($idpaket)
	{
		$strXML = '';
		$strXML .= '<graph yAxisName=\'Presentase\' caption=\'Grafik Progress Fisik Pelaksanaan Paket\' subcaption=\'Tahun '.$this->session->userdata('thn_anggaran').'\' hovercapbg=\'FFECAA\' hovercapborder=\'F47E00\' formatNumberScale=\'0\' decimalPrecision=\'0\' showvalues=\'0\' numdivlines=\'5\' numVdivlines=\'0\' yaxisminvalue=\'1000\' yaxismaxvalue=\'100\'  rotateNames=\'1\' NumberSuffix=\'%25\'>
					<categories >
						<category name=\'Jan\' />
						<category name=\'Feb\' />
						<category name=\'Mar\' />
						<category name=\'Apr\' />
						<category name=\'Mei\' />
						<category name=\'Jun\' />
						<category name=\'Jul\' />
						<category name=\'Agt\' />
						<category name=\'Sep\' />
						<category name=\'Okt\' />
						<category name=\'Nop\' />
						<category name=\'Des\' />
					</categories>';
		//grafik data rencana fisik
		$strXML .= '<dataset seriesName=\'Rencana Kontraktual\' color=\'1D8BD1\' anchorBorderColor=\'1D8BD1\' anchorBgColor=\'1D8BD1\'>';
		foreach($this->lmm->get_rencana_by_idpaket($idpaket) as $row)
		{
			$strXML .= '<set value="'.$row->rencana_kontraktual.'" />';
		}
		$strXML .= '</dataset>';
		$strXML .= '<dataset seriesName=\'Rencana Swakelola\' color=\'F1683C\' anchorBorderColor=\'F1683C\' anchorBgColor=\'F1683C\'>';
		foreach($this->lmm->get_rencana_by_idpaket($idpaket) as $row)
		{
			$strXML .= '<set value="'.$row->rencana_swakelola.'" />';
		}
		$strXML .= '</dataset>';

		//grafik data progress fisik
		$strXML .= '<dataset seriesName=\'Progress Kontraktual\' color=\'9ae5f1\' anchorBorderColor=\'9ae5f1\' anchorBgColor=\'9ae5f1\'>';
		foreach($this->lmm->get_progress_by_idpaket($idpaket)->result() as $row)
		{
			$strXML .= '<set value="'.$row->progress_kontraktual.'" />';
		}
		$strXML .= '</dataset>';
		$strXML .= '<dataset seriesName=\'Progress Swakelola\' color=\'f1c23c\' anchorBorderColor=\'f1c23c\' anchorBgColor=\'f1c23c\'>';
		foreach($this->lmm->get_progress_by_idpaket($idpaket)->result() as $row)
		{
			$strXML .= '<set value="'.$row->progress_swakelola.'" />';
		}
		$strXML .= '</dataset>';
		$strXML .= '</graph>';
		$myFile = dirname(dirname(dirname(dirname(__FILE__)))).'/charts/testFile.xml';
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $strXML);
		fclose($fh);
		$graph = '<script type="text/javascript">
					   var chart = new FusionCharts("'.base_url().'charts/FCF_MSLine.swf", "ChartId", "600", "350");
					   chart.setDataURL("'.base_url().'charts/testFile.xml");		   
					   chart.render("chartdiv");
				  </script>';
		$data['graph'] = $graph;
		$data['idpaket'] = $idpaket;
		$this->load->view('e-monev/grafik_progress', $data);
	}

	function daftar_masalah($d_skmpnen_id)
	{
            $data_masalah = array();
            foreach($this->bulan() as $key=>$value)
            {
                $jml = $this->lmm->count_permasalahan($d_skmpnen_id, $key);
                $data_masalah[] = array('bulan'=>$key, 'nama_bulan'=>$value, 
                        'jml_permasalahan'=> $jml->num_rows() > 0 ? $jml->row()->jml_permasalahan:0
                    );
            }
            $data['d_skmpnen_id'] = $d_skmpnen_id;
            $data['sub_komponen'] = $this->lmm->get_sub_komponen_by_id($d_skmpnen_id)->row()->urskmpnen;
            $data['daftar_permasalahan'] = $data_masalah;
            $this->load->view('e-monev/grid_permasalahan',$data);
	}
	
	function coba_ajax()
	{
		$data['data1'] = $this->data_dummy_array();
		$data['content'] = $this->load->view('e-monev/coba_ajax',$data,true);
		$this->load->view('main',$data);
	}
	
	function save_masalah($id, $bulan)
	{
		$permasalahan = $this->input->post('permasalahan');
		$pihak_terkait = $this->input->post('pihak_terkait');
		$ket_pihak_terkait = $this->input->post('ket_pihak_terkait');
		$status = $this->input->post('status');
		$data = array(
                    'd_skmpnen_id' => $id,
                    'thang' => $this->session->userdata('thn_anggaran'),
					'bulan' => $bulan,
				'isi_permasalahan'=> $permasalahan,
				'pihak_terkait'=> $pihak_terkait,
				'ket_pihak_terkait'=> $ket_pihak_terkait,
				'status'=> $status
					);
		$this->lmm->add($data);
	}
        
    function save_penyelesaian($id_permasalahan)
	{
		$penyelesaian = $this->input->post('detail_penyelesaian');
        $data = array(
            'id_permasalahan' => $id_permasalahan,
            'detail_penyelesaian' => $penyelesaian
        );
        $this->lmm->add_penyelesaian($data);
	}
	
			
	function save_feedback($id_permasalahan){

		$data = array(
			'ID_PERMASALAHAN' => $id_permasalahan,
			'ID_USER' => $this->input->post('id_user'),
			'PESAN' => $this->input->post('feedback_text'),
			'PARENT' => $this->input->post('reply_form'),
			'STATUS' => 1,
		);
		$this->fm->save($data);
	}
        
    function update_masalah()
	{
        $id_permasalahan = $this->input->post('id_permasalahan');
        $permasalahan = $this->input->post('permasalahan');
		$pihak_terkait = $this->input->post('pihak_terkait');
		$ket_pihak_terkait = $this->input->post('ket_pihak_terkait');
		$status = $this->input->post('status');
		$data = array(
				'isi_permasalahan'=> $permasalahan,
				'pihak_terkait'=> $pihak_terkait,
				'ket_pihak_terkait'=> $ket_pihak_terkait,
				'status'=> $status
					);
		$this->lmm->update($id_permasalahan, $data);
	}
        
    function update_penyelesaian($id_penyelesaian)
	{
		$penyelesaian = $this->input->post('detail_penyelesaian');
        $data = array(
            'detail_penyelesaian' => $penyelesaian
        );
        $this->lmm->update_penyelesaian($id_penyelesaian, $data);
	}
        
    function hapus_penyelesaian()
	{
        if ($this->input->post('id_penyelesaian') != NULL && $this->input->post('id_penyelesaian') != ''){
            $this->lmm->hapus_penyelesaian($this->input->post('id_penyelesaian'));
        }
	}
	
	function cek_dropdown($value)
	{
		if($value == 0)
		{
			//$this->form_validation->set_message('cek_dropdown', 'Kolom %s harus dipilih!!');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	function form_input_referensi($id)
	{
		$result = $this->lmm->get_referensi_by_id($id)->row();
		$data['tanggal'] = $result->tanggal;
		$data['referensi_id'] = $id;
		$this->load->view('e-monev/form_input_ref',$data);
	}
	
	function form_input_masalah($id,$bulan)
	{
		$data['pihak_terkait'] = $this->data_pihak_terkait();
		$data['status'] = $this->data_status();
		$result = $this->lmm->get_permasalahan_byBulan($id, $bulan);
		$array_bulan = $this->bulan();
                
                $data['sub_komponen'] = $this->lmm->get_sub_komponen_by_id($id)->row()->urskmpnen;
		$data['daftar_permasalahan'] = $result;
		$data['bulan'] = $array_bulan[$bulan];
                $data['idbulan'] = $bulan;
                $data['d_skmpnen_id'] = $id;
		$this->load->view('e-monev/grid_permasalahan_bulanan',$data);
	}
        
        function get_upaya_penyelesaian($id_permasalahan){
            $data_masalah = $this->lmm->get_permasalahan_by_id($id_permasalahan);
            if ($data_masalah->num_rows() > 0){
		$array_bulan = $this->bulan();
                
                $data['sub_komponen'] = $this->lmm->get_sub_komponen_by_id($data_masalah->row()->d_skmpnen_id)->row()->urskmpnen;
		$data['data_masalah'] = $data_masalah->row();
                $data['daftar_penyelesaian'] = $this->lmm->get_upaya_penyelesaian_by_masalah($id_permasalahan);
		$data['bulan'] = $array_bulan[$data_masalah->row()->bulan];
		$this->load->view('e-monev/grid_upaya_penyelesaian',$data);
            }else
                echo 'Invalid id or param';
        }
		
		
        function input_feedback($id_permasalahan){
            $data_masalah = $this->lmm->get_permasalahan_by_id($id_permasalahan);
			$feedback = $this->fm->get_history($id_permasalahan); 
            if ($data_masalah->num_rows() > 0){
				$array_bulan = $this->bulan();
                
                $data['sub_komponen'] = $this->lmm->get_sub_komponen_by_id($data_masalah->row()->d_skmpnen_id)->row()->urskmpnen;
				$data['data_masalah'] = $data_masalah->row();
                $data['daftar_penyelesaian'] = $this->lmm->get_upaya_penyelesaian_by_masalah($id_permasalahan);
				$data['id_user'] = $this->session->userdata('id_user');
				$data['history'] = $feedback;
				$data['id_permasalahan'] = $id_permasalahan;
				$data['bulan'] = $array_bulan[$data_masalah->row()->bulan];
				$this->load->view('e-monev/grid_feedback',$data);
            }else
                echo 'Invalid id or param';
        }
		
		
		function load_more($id_permasalahan) {
			$tmp = '';
			if(isSet($_POST['id_feedback'])){
				$id_feedback = $_POST['id_feedback'];
				$feedback = $this->fm->get_more($id_permasalahan,$id_feedback);
				$idf = '';
				$tmp = '';
				foreach($feedback->result() as $row){

					$komentar_aktif = '<a href="#repnow" onclick="javascript:replyFrom('.$row->ID_FEEDBACK.')" class="reply-stream" id="reply-stream-'.$row->ID_FEEDBACK.'">komentar</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

					$tmp .= '<li>
						<a href="#"><img class="ava-stream" src="'.base_url().'images/icons/depkes.png" width="40" height="48" alt="'.$row->USERNAME.'" /></a>
						<div class="pesan-stream">
							<div class="nama-stream">'.strtolower($row->USERNAME).'</div>
							<div id="msg-stream-'.$row->ID_FEEDBACK.'">'.$row->PESAN.'</div>
							<div class="date-stream">
								'.$komentar_aktif.'
								<a title="'.date("d F Y   H:i ", strtotime($row->TANGGAL)).'WIB">'.$this->general->KonversiWaktu(strtotime($row->TANGGAL)).'</a>
							</div>
						</div>
						<div class="clear"></div>
					</li>';

					if($row->PARENT == 0){
						$parent = $this->fm->get_parent($id_permasalahan, $row->ID_FEEDBACK); 
						foreach ($parent->result() as $brs):						
							$tmp .= '<li class="msg-stream-reply">
								<a href="#"><img class="ava-stream-reply" src="'.base_url().'images/icons/depkes.png" width="30" height="38" alt="'.$brs->USERNAME.'" /></a>
								<div class="pesan-stream-reply">
									<div class="nama-stream-reply">'.(strtolower($brs->USERNAME)).'</div>
									<div>'.$brs->PESAN.'</div>
									<div class="date-stream-reply">
										<a title="'.date("d F Y   H:i ", strtotime($brs->TANGGAL)).'WIB">'.$this->general->KonversiWaktu(strtotime($brs->TANGGAL)).'</a>
									</div>
								</div>
								<div class="clear"></div>
							</li>';	
						endforeach; 
					}


					$idf = $row->ID_FEEDBACK;
				}

				if($feedback->num_rows() < 5){
					$tmp .= '<a href="#judul">
								<div class="morebox">kembali ke atas</div>
							</a>';
				}else{
					$tmp .= '<a href="#" class="moro" id="'.$idf.'" onclick="getMore('.$idf.')">
								<div id="moro'.$idf.'" class="morebox">selanjutnya</div>
							</a>';
				}
			}

			echo $tmp;
		}



        function get_masalah($id){
            $data = $this->lmm->get_permasalahan_by_id($id);
            if ($data->num_rows() > 0){
                $result = $data->result_array();
                header('Content-type: application/json');
                echo json_encode($result[0]);
            }else
                echo json_encode (array());
        }
        
        function get_penyelesaian($id){
            $data = $this->lmm->get_upaya_penyelesaian_by_id($id);
            if ($data->num_rows() > 0){
                $result = $data->result_array();
                header('Content-type: application/json');
                echo json_encode($result[0]);
            }else
                echo json_encode (array());
        }

        function upload_file($field_name)
	{	
		$config['upload_path'] = './file';
		$config['allowed_types'] ='doc|docx|pdf|txt|jpg|jpeg';
		
		$this->load->library('upload', $config);		
		$files = $this->upload->do_upload($field_name);	
				
		$out = '';		
		if (  ! $files ){
			$out .= array('error' => $this->upload->display_errors());
			return "";
		}	
		else{
			$data = $this->upload->data($field_name);
			$file_name = $data['file_name'];
			$path[0] = 'file/'.$file_name;
			$path[1] = $file_name;
			return $path;
		}
	}
	
	function unggah($d_skmpnen_id)
	{
		$data2['d_skmpnen_id'] = $d_skmpnen_id;
		$data2['error_file'] = '';
		if($this->session->userdata('upload_file') != ''){
			$data2['error_file'] = $this->session->userdata('upload_file');
			$this->session->unset_userdata('upload_file');
		}
		$data['content'] = $this->load->view('e-monev/form_unggah_dokumen',$data2,true);
		$this->load->view('main',$data);
	}
	
	function do_unggah($d_skmpnen_id)
	{
		$file = null;
		$config['upload_path'] = "./file";
		$config['allowed_types'] ='doc|docx|pdf|xls|xlsx|txt';
		$config['max_size']	= '10000';
							
		// create directory if doesn't exist
		if(!is_dir($config['upload_path']))
		mkdir($config['upload_path'], 0777);
			
		$this->load->library('upload', $config);
		
			if(!$this->upload->do_upload('file_unggah')){
				$data_file = $this->upload->data();
				$notif_upload = '<font color="red"><b>'.$this->upload->display_errors("<p>Error Upload : ", "</p>").$data_file['file_type'].'</b></font>';
				$this->session->set_userdata('upload_file', $notif_upload);
				redirect('e-monev/laporan_monitoring/unggah/'.$d_skmpnen_id);
			}else{
				$data_file = $this->upload->data();
				if($data_file['file_size'] > 0) $file = $data_file['file_name'];
			}
		
		$data = array(
			'd_skmpnen_id' => $d_skmpnen_id,
			'nama_dokumen' => $file,
			'keterangan'   => $this->input->post('ket_file')
		);
		/*
		if($this->lmm2->get_dokumen($d_skmpnen_id)->num_rows()>0){
			$file_gambar = $data_file['file_path'].$this->lmm2->get_dokumen($d_skmpnen_id)->row()->nama_dokumen;
			if(is_file($file_gambar)){
				unlink($file_gambar);
			}
			$this->lmm2->unggah($data,1);
		}else{
			$this->lmm2->unggah($data,2);
		}*/
		$this->lmm2->unggah($data,2);
		
		redirect('e-monev/laporan_monitoring/daftar_dokumen/'.$d_skmpnen_id);
		
	}
	
	function daftar_dokumen($d_skmpnen_id)
	{
		$colModel['NO'] = array('No.',30,TRUE,'center',0);
		$colModel['keterangan'] = array('Keterangan',300,TRUE,'left',1);
		$colModel['nama_dokumen'] = array('Nama File',300,TRUE,'left',1);
		$colModel['HAPUS'] = array('Hapus',50,FALSE,'center',0);
		$colModel['UNDUH'] = array('Unduh',50,FALSE,'center',0);
		
		//setting konfigurasi pada bottom tool bar flexigrid
		$gridParams = array(
							'width' => 'auto',
							'height' => 330,
							'rp' => 15,
							'rpOptions' => '[15,30,50,100]',
							'pagestat' => 'Menampilkan : {from} ke {to} dari {total} data.',
							'blockOpacity' => 0,
							'title' => '',
							'showTableToggleBtn' => false,
							'nowrap' => false
							);
		//menambah tombol pada flexigrid top toolbar
		$buttons[] = array('Kembali','delete','spt_js');
		$buttons[] = array('Unggah','add','spt_js');
	
		//mengambil data dari file controler ajax pada method grid_region		
		$url = site_url()."/e-monev/laporan_monitoring/grid_dokumen/".$d_skmpnen_id;
		$grid_js = build_grid_js('user',$url,$colModel,'ID','asc',$gridParams,$buttons);
		$data['js_grid'] = $grid_js;
		$data['added_js'] = 
		"<script type='text/javascript'>
		function spt_js(com,grid){	
			if (com=='Unggah'){
				location.href= '".site_url()."/e-monev/laporan_monitoring/unggah/".$d_skmpnen_id."';    
			}
			if (com=='Kembali'){
				location.href= '".site_url()."/e-monev/laporan_monitoring/';    
			}
		}
		function hapus(hash){
				if(confirm('Anda yakin ingin menghapus dokumen ini?')){
					location.href='".site_url()."/e-monev/laporan_monitoring/hapus_dokumen/'+hash;
				}
			}
		</script>";
			
		//$data['added_js'] variabel untuk membungkus javascript yang dipakai pada tombol yang ada di toolbar atas
		$data['notification'] = "";
		if($this->session->userdata('notification')!=''){
			$data['notification'] = "
				<script>
					$(document).ready(function() {
						$.growlUI('Pesan :', '".$this->session->userdata('notification')."');
					});
				</script>
			";
		}//end if

		$data['judul'] = 'Daftar Dokumen';
		$data['content'] = $this->load->view('grid',$data,true);
		$this->load->view('main',$data);
	}
	
	function grid_dokumen($d_skmpnen_id) 
	{
		$valid_fields = array('keterangan','nama_dokumen','data_dokumen_id');
		$this->flexigrid->validate_post('data_dokumen_id','asc',$valid_fields);
		$records = $this->lmm2->get_grid_dokumen($d_skmpnen_id);
		
		$this->output->set_header($this->config->item('json_header'));
		
		$no =0;
		foreach ($records['records']->result() as $row){
				$no = $no+1;
				$record_items[] = array(
										$row->data_dokumen_id,
										$no,
										$row->keterangan,
										$row->nama_dokumen,
										'<img style="cursor:pointer" src="'.base_url().'images/flexigrid/delete.gif" onclick="hapus(\''.$row->data_dokumen_id.'\')">',
										'<a href='.base_url().'file/'.$row->nama_dokumen.'><img border=\'0\' src=\''.base_url().'images/icon/download2.png\'></a>'
								);
		}
		if(isset($record_items))
			$this->output->set_output($this->flexigrid->json_build($records['record_count'],$record_items));
		else
			$this->output->set_output('{"page":"1","total":"0","rows":[]}');
	}
	
	function hapus_dokumen($data_dokumen_id){
		$upload_path = 'file/';
		$row = $this->lmm2->get_dokumen_by_id($data_dokumen_id)->row();
		$file_gambar = $upload_path.$row->nama_dokumen;
		$d_skmpnen_id = $row->d_skmpnen_id;
		if(is_file($file_gambar)){
			unlink($file_gambar);
		}
		$this->lmm2->hapus_dokumen($data_dokumen_id);
		redirect('e-monev/laporan_monitoring/daftar_dokumen/'.$d_skmpnen_id);
	}

}//end class

/* End of file home.php */
/* Location: ./system/application/controllers/home.php */
