import os

files = [
    'generate_kuantitatif_annual_pdf.php',
    'generate_kuantitatif_pdf.php',
    'generate_kuantitatif_quarterly_pdf.php',
    'generate_umpan_balik_annual_pdf.php',
    'generate_umpan_balik_pdf.php',
    'generate_umpan_balik_quarterly_pdf.php',
    'skp/generate_skp_akhir_pdf.php'
]

replacement_utama = """            // Reset for second pass (rendering)
            $row_number = 1;
            $prev_rhk = null;
            $prev_rencana = null;
            $rhk_group_remaining = 0;
            $rencana_group_remaining = 0;
            
            foreach ($kinerja_utama as $index => $row): 
                $current_rhk = $row['RHK_PIMPINAN_INTERV'] ?? '';
                $current_rencana = $row['RENCANA_HASIL_KERJA'] ?? '';
                $is_first_in_group_rhk = ($current_rhk !== $prev_rhk);
                $is_first_in_group_rencana = ($current_rencana !== $prev_rencana);
                
                if ($is_first_in_group_rhk) {
                    $rhk_group_remaining = $rhk_rowspan_map[$index] ?? 1;
                }
                if ($is_first_in_group_rencana) {
                    $rencana_group_remaining = $rencana_rowspan_map[$index] ?? 1;
                }
                
                $is_last_in_group_rhk = ($rhk_group_remaining == 1);
                $is_last_in_group_rencana = ($rencana_group_remaining == 1);
                
                $should_show_rhk = $is_first_in_group_rhk;
                $should_show_rencana = $is_first_in_group_rencana;
                
                // Increment row number only when starting a new RHK_PIMPINAN_INTERV group
                if ($is_first_in_group_rhk && $prev_rhk !== null) {
                    $row_number++;
                } elseif ($is_first_in_group_rhk && $prev_rhk === null) {
                    // First row, keep row_number as 1
                }
                
                $rhk_class = "";
                if (!$should_show_rhk) { $rhk_class .= " no-top-border"; }
                if (!$is_last_in_group_rhk) { $rhk_class .= " no-bottom-border"; }
                
                $rencana_class = "";
                if (!$should_show_rencana) { $rencana_class .= " no-top-border"; }
                if (!$is_last_in_group_rencana) { $rencana_class .= " no-bottom-border"; }
                
                $rhk_group_remaining--;
                $rencana_group_remaining--;
            ?>
            <tr>
                <td class="center <?= $rhk_class ?>"><?= $should_show_rhk ? $row_number : '' ?></td>
                <td class="<?= $rhk_class ?>"><?= $should_show_rhk ? nl2br(htmlspecialchars($current_rhk)) : '' ?></td>
                <td class="<?= $rencana_class ?>"><?= $should_show_rencana ? nl2br(htmlspecialchars($current_rencana)) : '' ?></td>"""

replacement_tambahan = replacement_utama.replace('$kinerja_utama', '$kinerja_tambahan')

target_str_utama = """            // Reset for second pass (rendering)
            $row_number = 1;
            $prev_rhk = null;
            $prev_rencana = null;
            
            foreach ($kinerja_utama as $index => $row): 
                $current_rhk = $row['RHK_PIMPINAN_INTERV'] ?? '';
                $current_rencana = $row['RENCANA_HASIL_KERJA'] ?? '';
                $is_first_in_group_rhk = ($current_rhk !== $prev_rhk);
                $is_first_in_group_rencana = ($current_rencana !== $prev_rencana);
                $rowspan_count_rhk = $rhk_rowspan_map[$index] ?? 1;
                $rowspan_count_rencana = $rencana_rowspan_map[$index] ?? 1;
                $should_show_rhk = $is_first_in_group_rhk;
                $should_show_rencana = $is_first_in_group_rencana;
                
                // Increment row number only when starting a new RHK_PIMPINAN_INTERV group
                if ($is_first_in_group_rhk && $prev_rhk !== null) {
                    $row_number++;
                } elseif ($is_first_in_group_rhk && $prev_rhk === null) {
                    // First row, keep row_number as 1
                }
            ?>
            <tr>
                <?php if ($should_show_rhk): ?>
                    <td class="center <?= $rowspan_count_rhk > 10 ? 'large-rowspan' : '' ?>" rowspan="<?= $rowspan_count_rhk ?>"> <?= $row_number ?> </td>
                <?php endif; ?>
                <?php if ($should_show_rhk): ?>
                    <td class="<?= $rowspan_count_rhk > 10 ? 'large-rowspan' : '' ?>" rowspan="<?= $rowspan_count_rhk ?>"><?= nl2br(htmlspecialchars($current_rhk)) ?></td>
                <?php endif; ?>
                <?php if ($should_show_rencana): ?>
                    <td class="<?= $rowspan_count_rencana > 10 ? 'large-rowspan' : '' ?>" rowspan="<?= $rowspan_count_rencana ?>"><?= nl2br(htmlspecialchars($current_rencana)) ?></td>
                    <?php endif; ?>"""

target_str_tambahan = target_str_utama.replace('$kinerja_utama', '$kinerja_tambahan')

for f in files:
    with open(f, 'r', encoding='utf-8') as file:
        content = file.read()
    
    content = content.replace('<td rowspan="1" style="border: none;"> <?= $ekspektasi_isi ?> </td>', '<td style="border: none;"> <?= $ekspektasi_isi ?> </td>')
    content = content.replace('<td rowspan="1" style="border: none;" class="feedback-cell"> <?= $umpanbalik_isi ?> </td>', '<td style="border: none;" class="feedback-cell"> <?= $umpanbalik_isi ?> </td>')
    
    content = content.replace(target_str_utama, replacement_utama)
    content = content.replace(target_str_tambahan, replacement_tambahan)
    
    with open(f, 'w', encoding='utf-8') as file:
        file.write(content)
        
print("Replacement completed.")
