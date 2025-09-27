<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Joel Roy Perangin-angin, SST',
            'username' => 'joelroy',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'foto' => null,
            'jabatan' => 'Kepala BPS Kabupaten Dairi',
            'nip' => '1.97701212003121e+17',
        ]);
        User::create([
            'name' => 'Marudut Silaban, SE, MM',
            'username' => 'marudutsilaban',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Marudut_1748395009.png',
            'jabatan' => 'Statistisi Ahli Muda',
            'nip' => '1.97111141993031e+17',
        ]);
        User::create([
            'name' => 'Efendy Girsang, SP',
            'username' => 'efendygirsang',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Efendy_1748395130.png',
            'jabatan' => 'Statistisi Ahli Pertama',
            'nip' => '1.97410241994011e+17',
        ]);
        User::create([
            'name' => 'Emmi Manurung, S.E.',
            'username' => 'emmimanurung',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Emmi_Manurung_1748395166.png',
            'jabatan' => 'Statistisi Ahli Pertama',
            'nip' => '1.97607061999032e+17',
        ]);
        User::create([
            'name' => 'Tetty Saorma Hasiholan, S.Si',
            'username' => 'tettysaorma',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Tetty_Simanjuntak_1750063784.png',
            'jabatan' => 'Statistisi Ahli Pertama',
            'nip' => '1.97705242006042e+17',
        ]);
        User::create([
            'name' => 'Marlon Aritonang, S.Si.,M.M.',
            'username' => 'marlonaritonang',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Marlon_Aritonang_1750063354.png',
            'jabatan' => 'Kepala Subbagian Umum',
            'nip' => '1.97708162011011e+17',
        ]);
        User::create([
            'name' => 'Zunkifli Haryanto Yoko Ningrat Siahaan',
            'username' => 'zunkiflisiahaan',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Zunkifli_Siahaan_1750063830.png',
            'jabatan' => 'Statistisi Terampil',
            'nip' => '1.98001312006041e+17',
        ]);
        User::create([
            'name' => 'Richardo Tober Sihombing, S.Si',
            'username' => 'richardo',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Richardo_Sihombing_1750063596.png',
            'jabatan' => 'Statistisi Ahli Muda',
            'nip' => '1.98403122011011e+17',
        ]);
        User::create([
            'name' => 'Marisi Theresa E.D.',
            'username' => 'marisi.simamora',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Marisi_Theresa_1750063291.png',
            'jabatan' => 'Fungsional umum/Pengolah Data',
            'nip' => '1.98408172008012e+17',
        ]);
        User::create([
            'name' => 'Jesica Kristiyani Butar Butar',
            'username' => 'jcsando',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Jesica_Butar_Butar_1750063187.png',
            'jabatan' => 'Fungsional umum/Pengolah Data',
            'nip' => '1.98701192008012e+17',
        ]);
        User::create([
            'name' => 'Ratih Kurniawati, S.E.',
            'username' => 'ratihkurniawati',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Ratih_Kurniawati_1750063540.png',
            'jabatan' => 'Statistisi Ahli Pertama',
            'nip' => '1.98705282006042e+17',
        ]);
        User::create([
            'name' => 'Anwar Sinaga, A.Md.',
            'username' => 'anwarsinaga-pppk',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/_1750062963.jpeg',
            'jabatan' => 'Pranata Komputer Terampil',
            'nip' => '1.99002092024211e+17',
        ]);
        User::create([
            'name' => 'Ribka Anggina Tarigan, SST',
            'username' => 'ribka.anggina',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Ribka_T_1748330110.png',
            'jabatan' => 'Statistisi Ahli Pertama',
            'nip' => '1.99007012013112e+17',
        ]);
        User::create([
            'name' => 'Monika Yuniati Tampubolon, A.Md.',
            'username' => 'monika.yuniati',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Monika_T_1748394965.png',
            'jabatan' => 'Statistisi Terampil',
            'nip' => '1.99506062022032e+17',
        ]);
        User::create([
            'name' => 'Junjun Wijaya, S.Stat.',
            'username' => 'junjun.wijaya',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Junjun_Wijaya_1750063247.png',
            'jabatan' => 'Statistisi Ahli Pertama',
            'nip' => '1.99604112019031e+17',
        ]);
        User::create([
            'name' => 'Sangaptua Deo Datus Sagala, S.Tr.Stat.',
            'username' => 'deo.datus',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Sangaptua_Sagala_1757401638.png',
            'jabatan' => 'Statistisi Ahli Pertama',
            'nip' => '1.99701062019121e+17',
        ]);
        User::create([
            'name' => 'Juando Siallagan, S.Tr.Stat.',
            'username' => 'juando.siallagan',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Juando_Siallagan_1750062558.png',
            'jabatan' => 'Statistisi Ahli Pertama',
            'nip' => '1.99807012023021e+17',
        ]);
        User::create([
            'name' => 'Rivaldi, S.Tr.Stat.',
            'username' => 'rivaldi',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Rivaldi_1750063641.png',
            'jabatan' => 'Pranata Komputer Ahli Pertama',
            'nip' => '1.99809062022011e+17',
        ]);
        User::create([
            'name' => 'Monika Stevany Manurung, S.Tr.Stat.',
            'username' => 'monika.manurung',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Monika_Manurung_1750063492.png',
            'jabatan' => 'Statistisi Ahli Pertama',
            'nip' => '1.99902012022012e+17',
        ]);
        User::create([
            'name' => 'Sahnas Aisyiah Maha, S.Tr.Stat.',
            'username' => 'sahnas.aisyiah',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Sahnas_Maha_1750063692.png',
            'jabatan' => 'Statistisi Ahli Pertama',
            'nip' => '20107032023102e+17',
        ]);
        User::create([
            'name' => 'Mike Ervans Purba, A.Md.Stat.',
            'username' => 'mikepurba',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Mike_Purba_1750063449.png',
            'jabatan' => 'Statistisi Terampil',
            'nip' => '20110262023021e+17',
        ]);
        User::create([
            'name' => 'Jaksen Ferry Judo Lingga S.Si',
            'username' => 'jaksenlingga',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Jaksen_Lingga_1750063115.png',
            'jabatan' => 'Statistisi Ahli Muda',
            'nip' => '1.98310012006041e+17',
        ]);
        User::create([
            'name' => 'Frisca Ulina Br Munthe,S.Tr.Stat.',
            'username' => 'frisca.ulina',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Frisca_Munthe_1750062899.jpeg',
            'jabatan' => 'Statistisi Ahli Pertama',
            'nip' => '1.99707262019122e+17',
        ]);
        User::create([
            'name' => 'Binara Tua Simanjuntak',
            'username' => 'binara.simanjuntak',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Binara_Simanjuntak_1750063062.png',
            'jabatan' => 'Statistisi Terampil',
            'nip' => '1.98811012022031e+17',
        ]);
        User::create([
            'name' => 'Wiwik Rahayuni Lumban Gaol, A.Md',
            'username' => 'wiwikrahayuni@bps go.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'foto' => 'https://ppid.bps.go.id/upload/doc/Foto_Pegawai_Wiwik_Lumban_Gaol_1750063865.jpeg',
            'jabatan' => 'Pranata Komputer Mahir',
            'nip' => '1.98604022011012e+17',
        ]);
        User::create([
            'name' => 'Roganda Pasaribu',
            'username' => 'ganda',
            'password' => Hash::make('password123'),
            'role' => 'viewer',
            'foto' => null,
            'jabatan' => 'TAD',
            'nip' => 'nan',
        ]);
        User::create([
            'name' => 'Nelson Adriansyah Sibagariang',
            'username' => 'nelson',
            'password' => Hash::make('password123'),
            'role' => 'viewer',
            'foto' => null,
            'jabatan' => 'TAD',
            'nip' => 'nan',
        ]);
        User::create([
            'name' => 'Trio Linawati Tinambunan',
            'username' => 'trio',
            'password' => Hash::make('password123'),
            'role' => 'viewer',
            'foto' => null,
            'jabatan' => 'TAD',
            'nip' => 'nan',
        ]);
        User::create([
            'name' => 'Boas Matanari',
            'username' => 'boas',
            'password' => Hash::make('password123'),
            'role' => 'viewer',
            'foto' => null,
            'jabatan' => 'TAD',
            'nip' => 'nan',
        ]);
        User::create([
            'name' => 'Niko Rodes Sinaga',
            'username' => 'niko',
            'password' => Hash::make('password123'),
            'role' => 'viewer',
            'foto' => null,
            'jabatan' => 'TAD',
            'nip' => 'nan',
        ]);
    }
}
