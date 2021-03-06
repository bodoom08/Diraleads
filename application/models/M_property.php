<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'third_party/vendor/telnyx/telnyx-php/init.php');

class M_property extends CI_Model
{
    function getAllAreas()
    {
        return $this->db->get('areas')->result_array();
    }

    function getAllAttributes()
    {
        return $this->db->get('property_attributes')->result_array();
    }

    function getCustomPackageNames()
    {
        return $this->db
            ->select('id, name')
            ->where('status =', 'active')
            ->get('custom_package_names')
            ->result();
    }

    function get_virtual_number()
    {
        //assing virtual number
        // $result = $this->db->select('vn_id')->where('vn_id IS Not NULL', null, false)->get('properties')->result_array();
        // $vn_id_arr = array_column($result, 'vn_id');

        // $vn_number = $this->db->select('number')
        //     ->where_not_in('id', $vn_id_arr)
        //     ->order_by('id', 'ASC')
        //     ->get('virtual_numbers')
        //     ->row();


        if (!isset($vn_number)) { // Buy a new Telnyx number
            // $this->load->library('telnyx');
            $this->load->helper('telnyx_number');

            do {
                $numberResult = searchNumbersHelper('us', 'NY');
            } while (count($numberResult['result']) == 0);

            if (count($numberResult['result']) > 0) {
                $virtualNumber = $numberResult['result'][0]['number_e164'];
            } else {
                return ['type' => 'warning', 'text' => 'No virtual number was found, please contact admin!'];
            }
        } else {
            // $virtualNumber = $vn_number->number;
        }

        return ['type' => 'success', 'virtual_number' => $virtualNumber];
    }

    function property_listing()
    {
        array_walk_recursive($_POST, 'trim');

        extract($_POST);
        $available_date = $date;

        // if ($street && $area_id && $property_type && $price && $available_date && $property_desc) {
        if (empty($attribute_id) || empty($value)) {
            return ['type' => 'error', 'text' => 'Atleast one property attribute is mandatory!'];
        }

        if (strlen($property_desc) < 60) {
            return ['type' => 'error', 'text' => 'Description should have a minimum of 60 letters'];
        }

        // Check the property Image before upload
        if (!empty($_FILES)) {
            $this->load->library('upload');
            $files = $_FILES;
            $cpt = count($_FILES['userfile']['name']);
            $path = FCPATH . "/tmp_uploads";
            $config = array();
            $config['upload_path'] = $path;
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = '0';
            $config['overwrite'] = false;
            for ($i = 0; $i < $cpt; $i++) {
                $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $errors = $this->upload->display_errors();
                    return ['type' => 'error', 'text' => $errors];
                }
            }
        }
        if ($amenities) {
            $amenitie = implode(',', $amenities);
        } else {
            $amenitie = "";
        }
        $property_data = [
            'user_id' => $_SESSION['id'],
            'for' => 'short term rent',
            // 'house_number' => $house_no,
            'amenities' => $amenitie,
            'street' => $street,
            'area_id' => $area_id,
            'type' => $property_type,
            'price' => $price,
            'date_price' => $date_price,
            'available_date' => date('Y-m-d', strtotime($available_date)),
            'description' => $property_desc,
            'status' => 'active',
            'coords'    => isset($geolocation) ? $geolocation : "[]",
            'created_by' => $_SESSION['id'],
            // 'created_at' => date('Y-m-d H:i:s'),
            'manual_booking' => $manualBooking,
            'blocked_date' => $blockedDate,
            'is_annual' => $is_annual,
            'bedrooms'  => $value['bedrooms'],
            'bathrooms' => $value['bathrooms'],
            'florbas' => $value['florbas'],
            'area_other' => $value['area_other'],
            'sleep_number' => in_array('Sukkah', $amenities) ? $sleep_number : 0,
            'seasonal_price' => $is_annual == 'true' ? $seasonal_price['season'] : $seasonal_price['session']
        ];

        if ($is_annual == "true") {
            $property_data['days_price'] = $prices['days'];
            $property_data['weekend_price'] = $prices['weekend'];
            $property_data['weekly_price'] = $prices['weekly'];
            $property_data['monthly_price'] = $prices['monthly'];
            $property_data['private_note'] = $private_note['manual'];
            $property_data['weekend_from'] = $weekend_type['from'];
            $property_data['weekend_to'] = $weekend_type['to'];
            $property_data['only_weekend'] = isset($only_weekend) ? "true" : "false";
        } else {
            $property_data['private_note'] = $private_note['sessional'];
        }

        if (!$this->db->insert('properties', $property_data))
            return ['type' => 'error', 'text' => 'Error saving data'];

        $property_id = $this->db->insert_id();

        // $this->notifyToSubscriber($property_id, $property_data);

        foreach ($attribute_id as $key => $attribute) {
            $i = array_search($attribute, $attribute_id);
            if (!$value[$i]) {
                return ['type' => 'error', 'text' => 'You did not submit any value for property attribute!'];
            }
            $attribute_data[] = [
                'property_id' => $property_id,
                'attribute_id' => $attribute,
                'value' => $value[$i],
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        if ($this->db->insert_batch('property_attribute_values', $attribute_data)) {

            if (!empty($_FILES)) {
                $this->load->library('upload');
                $cpt = count($files['userfile']['name']);
                $path = FCPATH . "/uploads";
                $config = array();
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'jpg|jpeg|png';
                $config['max_size'] = '0';
                $config['overwrite'] = false;
                for ($i = 0; $i < $cpt; $i++) {
                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $errors = $this->upload->display_errors();
                        return ['type' => 'error', 'text' => $errors];
                    } else {
                        $dataupload = array('upload_data' => $this->upload->data());
                        $image_data[] = array(
                            'property_id' => $property_id,
                            'path' => $dataupload['upload_data']['file_name'],
                            'created_at' => date('Y-m-d H:i:s')
                        );
                    }
                }
                if (!$this->db->insert_batch('property_images', $image_data)) {
                    return ['type' => 'error', 'text' => 'Image upload is not done successfully!'];
                }
            }

            $vn = $this->db->select('*')
                ->where('number', $virtual_number)
                ->get('virtual_numbers')
                ->row();

            if (isset($vn)) { // Check if there is non-allocated Telnyx number in the table
                $this->load->helper('did');
                allocate_did($property_id, $vn->id, 'Auto Re-assign', 'DID re-allocation');
                $response['virtual_number'] = $vn->number;
            } else { // Buy a new Telnyx number
                $this->load->helper('telnyx_number');

                $numberOrders = createNumberOrdersHelper($virtual_number);

                if (is_array($numberOrders)) {
                    $this->db->insert('virtual_numbers', [
                        'number' => $virtual_number,
                        'details' => json_encode(myNumbersHelper($virtual_number))
                    ]);

                    $number_id = $numberOrders['id'];

                    $this->load->helper('did');

                    allocate_did($property_id, $this->db->insert_id(), 'Auto Assign', 'Auto DID allocation');
                    $response['virtual_number'] = $virtual_number;

                    \Telnyx\Telnyx::setApiKey(TELNYX_API_KEY);
                    \Telnyx\PhoneNumber::Update($virtual_number, [
                        "connection_id" => TEXML_APP_ID,
                    ]);
                    assign_messaging_profile($virtual_number);
                    // \Telnyx\PhoneNumber::Update($virtual_number, ["messaging_profile_id" => MESSAGE_PROFILE_ID]);
                } else {
                    return ['type' => 'warning', 'text' => 'Property submitted but can not be listed for number allocation error! Please contact admin'];
                }
            }

            return [
                'type' => 'success',
                'text' => 'Property listing done successfully!',
                'virtual_number' => $response['virtual_number']
            ];
        }

        return ['type' => 'error', 'text' => 'Please filled out all mandatory field!'];
    }

    public function getUserProperties()
    {
        $page = $this->input->get('page');
        $page = $page > 0 ? $page - 1 : 0;

        $this->db->start_cache();
        $this->db->select('a.*, b.number');
        $this->db->where('a.user_id', $_SESSION['id']);
        $this->db->from('properties a');
        $this->db->join('virtual_numbers b', 'b.id = a.vn_id', 'left');
        $this->db->stop_cache();
        $this->db->limit(10, $page * 10);

        $properties = $this->db->get()->result_array();
        $all_properties_count = $this->db->count_all_results();
        $this->db->flush_cache();

        if (count($properties) == 0) {
            return [];
        }
        $attributes =  $this->db
            ->select('a.text,a.icon,b.property_id,b.attribute_id,b.value ')
            ->where('a.id = b.attribute_id')
            ->where_in('b.property_id', array_column($properties, 'id'))
            ->get('property_attribute_values b,property_attributes a')
            ->result_array();
        $images_query = $this->db
            ->select('property_id, path')
            ->group_by('property_id')
            ->get('property_images');
        $images = array();
        if ($images_query !== FALSE && $images_query->num_rows() > 0) {
            $images = $images_query->result_array();
        }
        $images = array_column($images, 'path', 'property_id');
        array_walk($properties, function (&$property) use ($attributes, $images) {
            $property['images'] = (!empty($images[$property['id']])) ? $images[$property['id']] : '';
            $keys = array_keys(array_column($attributes, 'property_id'), $property['id']);
            $property['attributes'] = array_map(function ($key) use ($attributes) {
                return $attributes[$key];
            }, $keys);
        });
        return compact('properties', 'all_properties_count');
    }

    public function edit()
    {
        array_walk_recursive($_POST, 'trim');
        extract($this->input->post());

        $data['property_details'] = $this->db
            ->select('a.*, b.mobile, b.email')
            ->where('a.id', $user_property_id)
            ->where('a.user_id = b.id')
            ->get('properties a,users b')->row_array();
        $data['property_attributes'] = $this->db
            ->select('a.text,a.icon,b.property_id,b.attribute_id,b.value')
            ->where('a.id = b.attribute_id')
            ->where('b.property_id', $user_property_id)
            ->get('property_attribute_values b,property_attributes a')->result_array();
        $data['property_images'] = $this->db
            ->where('property_id', $user_property_id)
            ->get('property_images')->result_array();
        return $data;
    }

    function update()
    {
        array_walk_recursive($_POST, 'trim');

        extract($_POST);
        $available_date = $date;

        if (empty($attribute_id) || empty($value)) {
            return ['type' => 'error', 'text' => 'Atleast one property attribute is mandatory!'];
        }

        if (strlen($property_desc) < 60) {
            return ['type' => 'error', 'text' => 'Description should have a minimum of 60 letters'];
        }

        // remove property images
        $this->db->where('property_id', $property_id)->delete('property_images');

        // Check the property Image before upload
        if (!empty($_FILES)) {
            $this->load->library('upload');
            $files = $_FILES;
            $cpt = count($_FILES['userfile']['name']);
            $path = FCPATH . "/tmp_uploads";
            $config = array();
            $config['upload_path'] = $path;
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = '0';
            $config['overwrite'] = false;
            for ($i = 0; $i < $cpt; $i++) {
                $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $errors = $this->upload->display_errors();
                    return ['type' => 'error', 'text' => $errors];
                }
            }
        }
        if ($amenities) {
            $amenitie = implode(',', $amenities);
        } else {
            $amenitie = "";
        }
        $property_data = [
            'user_id' => $_SESSION['id'],
            'for' => 'short term rent',
            // 'house_number' => $house_no,
            'amenities' => $amenitie,
            'street' => $street,
            'area_id' => $area_id,
            'type' => $property_type,
            'price' => $price,
            'date_price' => $date_price,
            'available_date' => date('Y-m-d', strtotime($available_date)),
            'description' => $property_desc,
            'status' => 'active',
            'coords'    => isset($geolocation) ? $geolocation : "[]",
            'created_by' => $_SESSION['id'],
            // 'created_at' => date('Y-m-d H:i:s'),
            'manual_booking' => $manualBooking,
            'blocked_date' => $blockedDate,
            'is_annual' => $is_annual,
            'bedrooms'  => $value['bedrooms'],
            'bathrooms' => $value['bathrooms'],
            'florbas' => $value['florbas'],
            'area_other' => $value['area_other'],
            'sleep_number' => in_array('Sukkah', $amenities) ? $sleep_number : 0,
            'seasonal_price' => $is_annual == 'true' ? $seasonal_price['season'] : $seasonal_price['session']
        ];

        if ($is_annual == "true") {
            $property_data['days_price'] = $prices['days'];
            $property_data['weekend_price'] = $prices['weekend'];
            $property_data['weekly_price'] = $prices['weekly'];
            $property_data['monthly_price'] = $prices['monthly'];
            $property_data['private_note'] = $private_note['manual'];
            $property_data['weekend_from'] = $weekend_type['from'];
            $property_data['weekend_to'] = $weekend_type['to'];
            $property_data['only_weekend'] = isset($only_weekend) ? "true" : "false";
        } else {
            $property_data['private_note'] = $private_note['sessional'];
        }

        // if (!$this->db->insert('properties', $property_data)) {
        //     return ['type' => 'error', 'text' => 'Error saving data'];
        // }

        if (!$this->db->where('id', $property_id)->update('properties', $property_data)) {
            return ['type' => 'error', 'text' => 'Error updating data'];
        }
        foreach ($attribute_id as $key => $attribute) {
            $i = array_search($attribute, $attribute_id);
            if (!$value[$i]) {
                return ['type' => 'error', 'text' => 'You did not submit any value for property attribute!'];
            }
            $attribute_data[] = [
                'property_id' => $property_id,
                'attribute_id' => $attribute,
                'value' => $value[$i],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        $this->db->where('property_id', $property_id)->delete('property_attribute_values');
        if ($this->db->insert_batch('property_attribute_values', $attribute_data)) {
            if ($_FILES) {
                $this->load->library('upload');
                // $files = $_FILES;
                $cpt = count($files['userfile']['name']);
                $path = FCPATH . "/uploads";
                $config = array();
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'jpg|jpeg|png';
                $config['max_size'] = '0';
                $config['overwrite'] = false;
                for ($i = 0; $i < $cpt; $i++) {
                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $errors = $this->upload->display_errors();
                        return ['type' => 'error', 'text' => $errors];
                    } else {
                        $dataupload = array('upload_data' => $this->upload->data());
                        $image_data[] = array(
                            'property_id' => $property_id,
                            'path' => $dataupload['upload_data']['file_name'],
                            'created_at' => date('Y-m-d H:i:s')
                        );
                    }
                }
                if ($this->db->insert_batch('property_images', $image_data)) {
                    return ['type' => 'success', 'text' => 'Property Updated successfully!'];
                }
                return ['type' => 'error', 'text' => 'Image upload is not done successfully!'];
            }
            return ['type' => 'success', 'text' => 'Property Updated successfully!'];
        }
    }

    public function change_status()
    {
        array_walk_recursive($_POST, 'trim');
        extract($this->input->post());
        if ($this->db->set('status', 'IF(status = "active" , "inactive" , "active")', FALSE)->where('id', $property_id)->update('properties')) {
            return ['type' => 'success', 'text' => 'Property Status changed successfully!'];
        }
        return ['type' => 'error', 'text' => 'Error Occured! Please checked it manualy!'];
    }

    public function delete()
    {
        array_walk_recursive($_POST, 'trim');
        extract($this->input->post());
        if ($property_id) {
            $this->db->where('id', $property_id)->delete('properties');
            $this->db->where('property_id', $property_id)->delete('property_attribute_values');
            $this->db->where('property_id', $property_id)->delete('property_images');
            return ['type' => 'success', 'text' => 'Your Property Deleted successfully!'];
        }
        return ['type' => 'error', 'text' => 'Error Occured! Please checked it manualy!'];
    }

    public function mark_sold()
    {
        array_walk_recursive($_POST, 'trim');
        extract($this->input->post());
        if ($property_id) {
            $this->db->where('id', $property_id)->update('properties', ['vn_id' => NULL, 'flag' => 'true', 'sold' => 'true']);
            return ['type' => 'success', 'text' => 'Your Property Marked Sold successfully!'];
        }
        return ['type' => 'error', 'text' => 'Error Occured! Please checked it manualy!'];
    }

    public function notifyToSubscriber($id, $property)
    {
        $manual_booking_dates = json_decode($property['manual_booking']);
        $blocked_dates = json_decode($property['blocked_date']);

        $dates = array_merge($manual_booking_dates, $blocked_dates);

        $subscribers = $this->db->where('area_id', $property['area_id'])
            ->where('bedroom <=', $property['bedrooms']);

        foreach ($dates as $date) {
            $checkInDate = $date['checkInDate'];
            $checkOutDate = $date['checkOutDate'];
            $subscribers = $subscribers->where('date_from <=', $checkInDate)
                ->where('date_to >=', $checkOutDate);
        }

        $subscribers = $subscribers->get('subscribers');

        if ($subscribers !== FALSE && $subscribers->num_rows() > 0) {
            $subscribers = $subscribers->result_array();
            foreach ($subscribers as $subscriber) {
                $this->insertJob($id, $subscriber['user_id']);
            }
        }
    }

    public function insertJob($property_id, $subscriber_id)
    {
        $this->db->insert('rental_call_queue', [
            "property_id"   => $property_id,
            "subscriber_id" => $subscriber_id
        ]);

        $subscriber = $this->db->select('country_code, mobile')
            ->from('users')
            ->where('id', $subscriber_id)
            ->get()->row();
        $number = $subscriber->country_code . $subscriber->mobile;

        $this->load->helper('call');
        make_outbound_call($number);
    }

    public function getJobs()
    {
        $query = $this->db->get('rental_call_queue');

        if ($query !== FALSE && $query->num_rows() > 0)
            return $query->result_array();

        return false;
    }

    public function deleteJob($id)
    {
        return $this->db->where('id', $id)->delete('rental_call_queue');
    }

    public function existJob()
    {
        $query = $this->db->get('rental_call_queue');

        if ($query !== FALSE && $query->num_rows() > 0)
            return true;
        return false;
    }
}
