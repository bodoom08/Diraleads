<?php
defined('BASEPATH') or exit('No direct script access allowed');
class M_invoices extends CI_Model
{
    public function getdata()
    {
        extract($this->input->get());

        $search = array_map('trim', $search);
        $search['value'] = strtolower($search['value']);

        $this->db->start_cache();

        $query['select'] = ['a.created_at', 'a.description', "JSON_EXTRACT(a.package, '$.price')", "JSON_EXTRACT(a.package, '$.name')", 'a.result'];
        $this->db->select(
            "
            a.created_at,
            a.description,
            JSON_EXTRACT(a.package, '$.price') package_price,
            JSON_EXTRACT(a.package, '$.name') package_name,
            a.result,
            b.description as desc,
            b.price as price,
            b.package_name as user_package_name,
            b.package as user_package,
            a.id,
            b.id as pack_id"
        );
        $this->db->from('invoices a');
        $this->db->join('user_packages b', 'b.invoice_id = a.id', 'right');
        $this->db->where('b.user_id', $_SESSION['id']);

        $query['recordsTotal'] = $this->db->count_all_results();

        if (isset($search['value'])) {
            $this->db->group_start();
            foreach ($query['select'] as $s) {
                $this->db->or_like('LOWER(' . $s . ')', $search['value']);
            }
            $this->db->group_end();
        }

        $this->db->stop_cache();

        $this->db->order_by('a.created_at', 'desc');

        if ($length > 0) {
            $this->db->limit($length, $start);
        }
        $query['data'] = $this->db->get()->result_array();
        $query['draw'] = $draw;
        $query['recordsFiltered'] = $this->db->count_all_results();
        $this->db->flush_cache();
        unset($query['select']);
        return $query;
    }

    public function getById($id)
    {
        return $this->db
            ->select("a.*, JSON_EXTRACT(a.package, '$.price') as price, b.description as description")
            ->where('a.id', $id)
            ->where('b.user_id', $_SESSION['id'])
            ->from('invoices a')
            ->join('user_packages b', 'a.id = b.invoice_id', 'right')
            ->get()
            ->row();

    }
    public function getInvoiceById($id)
    {
        return $this->db
            ->select("a.*, JSON_EXTRACT(a.package, '$.price') as price")
            ->where('a.id', $id)
            ->from('invoices a')
            ->get()
            ->row();

    }
}
