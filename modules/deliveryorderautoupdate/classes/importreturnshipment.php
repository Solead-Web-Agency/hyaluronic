<?php
/**
* 2007-2023 Helloshop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

class ImportReturnShipment
{
    public function importColissimo($id_connnector)
    {
        $sql = "INSERT IGNORE INTO `"._DB_PREFIX_."hl_tracking_return`
        (`id_order`, `id_order_return`, `id_connector`, `shipping_number`, `date_add`)
        (SELECT co.id_order,ort.id_order_return, ".(int)$id_connector.", cl.shipping_number, cl.date_add
        FROM "._DB_PREFIX_."colissimo_label cl
        INNER JOIN "._DB_PREFIX_."colissimo_order co ON cl.id_colissimo_order=co.id_colissimo_order
        LEFT JOIN "._DB_PREFIX_."order_return ort ON co.id_order=ort.id_order
        WHERE cl.return_label <>0 ORDER BY cl.id_colissimo_order)";
        return Db::getInstance()->execute($sql);
    }
    public static function deleteReturn($ids)
    {
        if (is_array($ids) && count($ids)) {
            $ids = implode(', ', $ids);
            $sql = "DELETE FROM "._DB_PREFIX_.'hl_tracking_return WHERE id_return IN ('.pSQL($ids).')';
            return Db::getInstance()->execute($sql);
        } else {
            throw new Exception("ids not valid");
        }
    }
    public function checkColissimo()
    {
        $sql = "SELECT COUNT(*)
        FROM information_schema.tables WHERE table_schema = '"._DB_NAME_."'
        AND table_name = '"._DB_PREFIX_."colissimo_label'";
        return (int)Db::getInstance()->getValue($sql);
    }
    public function countrowsColissimo()
    {
        $sql = "SELECT COUNT(co.id_order)
        FROM "._DB_PREFIX_."colissimo_label cl
        INNER JOIN "._DB_PREFIX_."colissimo_order co ON cl.id_colissimo_order=co.id_colissimo_order
        LEFT JOIN "._DB_PREFIX_."order_return ort ON co.id_order=ort.id_order
        WHERE cl.return_label <>0 ORDER BY cl.id_colissimo_order";
        return Db::getInstance()->getValue($sql);
    }
    public static function updateConnector($id_connector, $ids_return)
    {
        if (is_array($ids_return) && count($ids_return)) {
            $ids = implode(', ', $ids_return);
            $sql = "UPDATE "._DB_PREFIX_."hl_tracking_return
            SET id_connector = ".(int)$id_connector."
            WHERE id_return IN (".pSQL($ids).")";
            return Db::getInstance()->execute($sql);
        } else {
            throw new Exception("ids not valid");
        }
    }
}
