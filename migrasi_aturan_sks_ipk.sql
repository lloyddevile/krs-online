-- Jalankan satu kali pada database yang sudah berisi aplikasi.
ALTER TABLE `mhs`
  ADD COLUMN `ipk_kumulatif` decimal(3,2) DEFAULT NULL;

ALTER TABLE `data_waktu`
  ADD COLUMN `ambang_ipk` decimal(3,2) DEFAULT NULL;
