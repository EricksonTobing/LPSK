<?php
declare(strict_types=1);

/**
 * Metadata tabel untuk generic CRUD & listing.
 * Kolom disaring agar sesuai izin & validasi sederhana.
 */
return [
  'permohonan' => [
    'label' => 'Data Permohonan Kasus',
    'pk' => 'no_reg_medan',
    'columns' => [
      'no_reg_medan'=> 'Nomor Registrasi Medan',
      'nama_pemohon' => 'Nama Pemohon',
      'jenis_kelamin' => 'Jenis Kelamin',
      'status_hukum' => 'Status Hukum',
      'tgl_pengajuan' => 'Tanggal Pengajuan',
      'pihak_perwakilan' => 'Pihak Perwakilan',
      'tindak_pidana' => 'Tindak Pidana',
      'petugas_penerima' => 'Petugas Penerima',
      'kelengkapan_berkas' => 'Kelengkapan Berkas',
      'media_pengajuan' => 'Media Pengajuan',
      'link_berkas_permohonan' => 'Link Berkas Permohonan',
      'jenis_perlindungan' => 'Jenis Perlindungan',
      'kab_kot_locus' => 'Kabupaten/Kota Locus',
      'provinsi' => 'Provinsi',
      'tempat_permohonan' => 'Tempat Permohonan'
    ],
    'searchable' => ['no_reg_medan','nama_pemohon','provinsi','tindak_pidana','media_pengajuan','tempat_permohonan'],
    'filters' => ['jenis_kelamin','media_pengajuan','provinsi','tempat_permohonan','tindak_pidana','status_hukum','pihak_perwakilan'],
  ],
  'penelaahan' => [
    'label' => 'Data Penelaahan Permohonan',
    'pk' => 'no_registrasi',
    'columns' => [
      'no_registrasi' => 'Nomor Registrasi',
      'no_reg_medan' => 'Nomor Registrasi Medan',
      'nama_pemohon' => 'Nama Pemohon',
      'jenis_kelamin' => 'Jenis Kelamin',
      'status_hukum' => 'Status Hukum',
      'tanggal_dispo' => 'Tanggal Disposisi',
      'proses_penalaahan' => 'Proses Penelaahan',
      'wilayah_perkara' => 'Wilayah Perkara',
      'layanan_dimohonkan' => 'Layanan Dimohonkan',
      'case_manager' => 'Case Manager',
      'tgl_berakhir_penelaahan' => 'Tanggal Berakhir Penelaahan',
      'waktu_tambahan' => 'Waktu Tambahan',
      'nama_ta_penalaahan' => 'Nama TA Penelaahan',
      'risalah_laporan' => 'Risalah Laporan'
    ],
    'searchable' => ['no_registrasi','no_reg_medan','wilayah_perkara','case_manager','nama_pemohon'],
    'filters' => ['risalah_laporan','nama_ta_penalaahan'],
    'joins' => [
      'permohonan' => ['no_reg_medan', 'no_reg_medan', ['nama_pemohon', 'jenis_kelamin', 'status_hukum']]
    ]
  ],
  'layanan' => [
    'label' => 'Data Layanan Kasus',
    'pk' => 'no_kep_smpl',
    'columns' => [
      'no_kep_smpl' => 'Nomor Keputusan SMPL',
      'no_reg_medan' => 'Nomor Registrasi Medan',
      'no_registrasi' => 'Nomor Registrasi',
      'no_spk' => 'Nomor SPK',
      'status_spk' => 'Status SPK',
      'status_hukum' => 'Status Hukum',
      'nama_terlindung' => 'Nama Terlindung',
      'jenis_tindak_pidana' => 'Jenis Tindak Pidana',
      'tanggal_disposisi' => 'Tanggal Disposisi',
      'case_manager' => 'Case Manager',
      'tgl_mulai_layanan' => 'Tanggal Mulai Layanan',
      'masa_layanan' => 'Masa Layanan',
      'tambahan_masa_layanan' => 'Tambahan Masa Layanan',
      'tgl_berakhir_layanan' => 'Tanggal Berakhir Layanan',
      'jenis_perlindungan' => 'Jenis Perlindungan',
      'wilayah_hukum' => 'Wilayah Hukum',
      'nama_ta_layanan' => 'Nama TA Layanan',
      'status' => 'Status'
    ],
    'searchable' => ['no_kep_smpl','no_spk','nama_terlindung','wilayah_hukum','jenis_tindak_pidana','status'],
    'filters' => ['status','jenis_tindak_pidana','wilayah_hukum','status_spk','nama_ta_layanan'],
    'joins' => [
      'permohonan' => ['no_reg_medan', 'no_reg_medan', ['status_hukum']],
      'penelaahan' => ['no_registrasi', 'no_registrasi', ['case_manager']]
    ]
  ],
  'anggaran' => [
    'label' => 'Data Anggaran',
    'pk' => 'kode_anggaran',
    'columns' => [
      'kode_anggaran' => 'Kode Anggaran',
      'nama_anggaran' => 'Nama Anggaran',
      'total_anggaran' => 'Total Anggaran'
    ],
    'searchable' => ['kode_anggaran','nama_anggaran'],
    'filters' => [],
  ],
  'pengeluaran' => [
    'label' => 'Data Pengeluaran',
    'pk' => 'nomor_kuintasi',
    'columns' => [
      'nomor_kuintasi' => 'Nomor Kuintasi', 
      'kode_anggaran' => 'Kode Anggaran',
      'jumlah' => 'Jumlah',
      'tanggal' => 'Tanggal',
      'keterangan' => 'Keterangan',
      'kode_mak' => 'Kode MAK',
    ],
    'searchable' => ['nomor_kuintasi','kode_anggaran','keterangan'],
    'filters' => [],
  ],
  'users' => [
    'label' => 'Data Pengguna',
    'pk' => 'id_user',
    'columns' => [
      'username' => 'Username',
      'password'  => 'Password',
      'nama_lengkap' => 'Nama Lengkap',
      'email' => 'Email',
      'role' => 'Role'
    ],
    'searchable' => ['username','nama_lengkap','email','role'],
    'filters' => ['role'],
  ]
];