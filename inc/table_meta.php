<?php
declare(strict_types=1);

return [
  'permohonan' => [
    'label' => 'Halaman Pemohon',
    'pk' => 'no_reg_medan',
    'columns' => [
        'no_reg_medan' => 'No Reg Medan',
        'nama_pemohon' => 'Nama Pemohon',
        'jenis_kelamin' => 'Jenis Kelamin',
        'status_hukum' => 'Status Hukum',
        'tgl_pengajuan' => 'Tgl Pengajuan',
        'pihak_perwakilan' => 'Pihak Perwakilan',
        'tindak_pidana' => 'Tindak Pidana',
        'id_pegawai' => 'Petugas Penerima',
        'kelengkapan_berkas' => 'Kelengkapan Berkas',
        'media_pengajuan' => 'Media Pengajuan',
        'link_berkas_permohonan' => 'Link Berkas Permohonan',
        'jenis_perlindungan' => 'Jenis Perlindungan',
        'kab_kot_locus' => 'Kab_Kota Locus',
        'provinsi' => 'Provinsi',
        'kab_kota_pemohon' => 'Kab_Kota Pemohon',
        'provinsi_pemohon' => 'Provinsi Pemohon',
        'tempat_permohonan' => 'Tempat Permohonan'
    ],
    'searchable' => ['no_reg_medan', 'nama_pemohon', 'tindak_pidana', 'media_pengajuan', 'jenis_perlindungan'],
    'filters' => ['jenis_kelamin', 'status_hukum', 'media_pengajuan', 'tempat_permohonan'],
    'joins' => [
        'pegawai' => ['id_pegawai', 'id_pegawai', ['nama_pegawai']]
    ]
],
  'penelaahan' => [
    'label' => 'Halaman Penelaahan',
    'pk' => 'no_registrasi',
    'columns' => [
        'no_registrasi' => 'No Registrasi',
        'no_reg_medan' => 'No Reg Medan',
        'nama_pemohon' => 'Nama Pemohon',
        'jenis_kelamin' => 'Jenis Kelamin',
        'kab_kota_pemohon' => 'Kab_Kota Pemohon',
        'provinsi_pemohon' => 'Provinsi Pemohon',
        'tindak_pidana' => 'Tindak Pidana',
        'status_hukum' => 'Status Hukum',
        'proses_hukum' => 'Proses Hukum',
        'tanggal_dispo' => 'Tgl Disposisi',
        'proses_penalaahan' => 'Proses Penalaahan',
        'kab_kot_locus' => 'Wilayah Perkara',
        'jenis_perlindungan' => 'Layanan Dimohonkan',
        'id_pegawai' => 'Case Manager',
        'tgl_berakhir_penelaahan' => 'Tgl Berakhir Penalaahan',
        'waktu_tambahan' => 'Waktu Tambahan',
        'nama_ta_penalaahan' => 'Nama TA Penalaahan',
        'risalah_laporan' => 'Risalah_ Laporan'
    ],
    'searchable' => ['no_registrasi', 'no_reg_medan', 'nama_pemohon', 'nama_ta_penalaahan'],
    'filters' => ['proses_hukum', 'risalah_laporan', 'nama_ta_penalaahan'],
    'joins' => [
        'pegawai' => ['id_pegawai', 'id_pegawai', ['nama_pegawai']],
        'permohonan' => ['no_reg_medan', 'no_reg_medan', [
            'nama_pemohon', 'jenis_kelamin', 'kab_kota_pemohon', 
            'provinsi_pemohon', 'tindak_pidana', 'status_hukum',
            'kab_kot_locus', 'jenis_perlindungan'
        ]]
    ]
],
  'layanan' => [
    'label' => 'Halaman Layanan',
    'pk' => 'no_kep_smpl',
    'columns' => [
        'no_kep_smpl' => 'No Kep SMPL',
        'no_reg_medan' => 'No Reg Medan',
        'no_registrasi' => 'No Registrasi',
        'no_spk' => 'No SPK',
        'tgl_no_kep_smpl' => 'Tgl No Kep SMPL',
        'status_spk' => 'Status SPK',
        'status_hukum' => 'Status Hukum',
        'nama_terlindung' => 'Nama Terlindung',
        'kab_kota_pemohon' => 'Kab_Kota Terlindung',
        'provinsi_pemohon' => 'Provinsi Terlindung',
        'jenis_tindak_pidana' => 'Jenis Tindak Pidana',
        'tanggal_disposisi' => 'Tgl Disposisi',
        'id_pegawai' => 'Case Manager',
        'tgl_mulai_layanan' => 'Tgl Mulai Layanan',
        'masa_layanan' => 'Masa Layanan',
        'tambahan_masa_layanan' => 'Tambahan Masa Layanan',
        'tgl_berakhir_layanan' => 'Tgl Berakhir Layanan',
        'jenis_perlindungan' => 'Jenis Perlindungan',
        'wilayah_hukum' => 'Wilayah Hukum',
        'nama_ta_layanan' => 'Nama TA Layanan',
        'status' => 'status'
    ],
    'searchable' => ['no_kep_smpl', 'no_spk', 'nama_terlindung', 'wilayah_hukum'],
    'filters' => ['status', 'jenis_tindak_pidana', 'status_spk', 'nama_ta_layanan'],
    'joins' => [
        'pegawai' => ['id_pegawai', 'id_pegawai', ['nama_pegawai']],
        'permohonan' => ['no_reg_medan', 'no_reg_medan', [
            'status_hukum', 'kab_kota_pemohon', 'provinsi_pemohon'
        ]],
        'penelaahan' => ['no_registrasi', 'no_registrasi', []]
    ]
],
  'pegawai' => [
    'label' => 'Data Pegawai',
    'pk' => 'id_pegawai',
    'columns' => [
      'id_pegawai' => 'ID Pegawai',
      'nama_pegawai' => 'Nama Pegawai',
      'jabatan' => 'Jabatan',
      'unit_kerja' => 'Unit Kerja',
      'email' => 'Email',
      'no_telp' => 'No. Telp',
      'aktif' => 'Status Aktif'
    ],
    'searchable' => ['nama_pegawai', 'jabatan', 'unit_kerja'],
    'filters' => ['jabatan', 'unit_kerja', 'aktif'],
  ],
  'anggaran' => [
    'label' => 'Data Anggaran',
    'pk' => 'kode_anggaran',
    'columns' => [
      'kode_anggaran' => 'Kode Anggaran',
      'nama_anggaran' => 'Nama Anggaran',
      'total_anggaran' => 'Total Anggaran',
      'tahun' => 'Tahun'
    ],
    'searchable' => ['kode_anggaran', 'nama_anggaran'],
    'filters' => ['tahun'],
  ],
  'mak' => [
    'label' => 'Data MAK',
    'pk' => 'kode_mak',
    'columns' => [
      'kode_mak' => 'Kode MAK',
      'nama_mak' => 'Nama MAK'
    ],
    'searchable' => ['kode_mak', 'nama_mak'],
    'filters' => [],
  ],
  'pengeluaran' => [
    'label' => 'Data Pengeluaran',
    'pk' => 'nomor_kuintasi',
    'columns' => [
      'nomor_kuintasi' => 'Nomor Kuitansi',
      'kode_anggaran' => 'Kode Anggaran',
      'jumlah' => 'Jumlah',
      'tanggal' => 'Tanggal',
      'kode_mak' => 'Kode MAK',
      'keterangan' => 'Keterangan'
    ],
    'searchable' => ['nomor_kuintasi', 'kode_anggaran', 'keterangan'],
    'filters' => ['kode_anggaran', 'kode_mak'],
    'joins' => [
      'anggaran' => ['kode_anggaran', 'kode_anggaran', ['nama_anggaran']],
      'mak' => ['kode_mak', 'kode_mak', ['nama_mak']]
    ]
  ],
  'users' => [
    'label' => 'Data Pengguna',
    'pk' => 'id_user',
    'columns' => [
      'username' => 'Username',
      'nama_lengkap' => 'Nama Lengkap',
      'email' => 'Email',
      'role' => 'Role'
    ],
    'searchable' => ['username', 'nama_lengkap', 'email', 'role'],
    'filters' => ['role'],
  ]
];