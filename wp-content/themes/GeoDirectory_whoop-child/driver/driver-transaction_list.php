<?php /* Template Name: driver-transaction_list */ ?>

<?php 

if(!strtotime($start_date) || !strtotime($end_date))
return;

?>

<script>
jQuery(document).ready(function() {
    jQuery('#tran_list').DataTable({
        "ordering": false
    });
});
</script>
<?php 

global $wpdb, $current_user;

$start_date  = date('Y-m-d', strtotime($start_date));
$end_date  = date('Y-m-d', strtotime($end_date));

$transaction_list = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM driver_transaction_details where driver_id = %d 
        AND transaction_date BETWEEN %s AND %s ", array($current_user->ID, $start_date, $end_date)
    )
);

$driver = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT balance, add_on_credit FROM driver where driver_id = %d ", array($current_user->ID)
    )
);

?>

<table id="tran_list" style="width:100%">
    <thead>
        <tr>
            <th>วันที่</th>
            <th>รายการ</th>
            <th>ถอน</th>
            <th>ฝาก</th>
            <th>คงเหลือ</th>
            <th>เครดิต</th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach ($transaction_list as $transaction) {
            echo "<tr>";
            echo "<td>".date('d/m/Y', strtotime($transaction->transaction_date))."</td>";
            echo "<td>".$transaction->transaction_type."</td>";
            echo "<td>".($transaction->debit != "" ? '---' : '').$transaction->debit."</td>";
            echo "<td>".($transaction->credit != "" ? '+++' : '').$transaction->credit."</td>";
            echo "<td>".$transaction->balance."</td>";
            echo "<td>".$transaction->balance_add_on_credit."</td>";
            echo "</tr>";
        }
    ?>
    </tbody>
    <tfoot>
        <tr>
            <th>วันที่</th>
            <th>รายการ</th>
            <th>ถอน</th>
            <th>ฝาก</th>
            <th>คงเหลือ</th>
            <th>เครดิต</th>
        </tr>
    </tfoot>
</table>
<br><br>
<table style="width:100%;text-align:right;">
    <tr>
        <td><b>คงเหลือทั้งหมด:</b></td><td><?php echo $driver->balance;?> บาท</td>
    </tr>
    <tr>
        <td><b>เครดิตทั้งหมด:</b></td><td><?php echo $driver->add_on_credit;?> พอยท์</td>
    </tr>
</table>