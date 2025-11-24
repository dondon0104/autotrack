<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class CarImageModel extends Model {
    protected $table = 'car_images';
    protected $primary_key = 'id';
    protected $fillable = [
        'car_id', 'color', 'image_path', 'is_primary', 'sort_order'
    ];

    public function __construct() {
        parent::__construct();
    }

    public function getByCarId($carId) {
        return $this->db->table($this->table)
                        ->where('car_id', $carId)
                        ->order_by('is_primary', 'DESC')
                        ->order_by('sort_order', 'ASC')
                        ->get_all();
    }

    public function getPrimaryByCarId($carId) {
        $row = $this->db->table($this->table)
                        ->where('car_id', $carId)
                        ->where('is_primary', 1)
                        ->limit(1)
                        ->get();
        return $row;
    }

    public function getByCarIdAndColor($carId, $color) {
        if (!$color) return null;
        $row = $this->db->table($this->table)
                        ->where('car_id', $carId)
                        ->where('color', $color)
                        ->order_by('is_primary', 'DESC')
                        ->order_by('sort_order', 'ASC')
                        ->limit(1)
                        ->get();
        return $row;
    }

    public function getColorsForCar($carId) {
        $rows = $this->db->table($this->table)
                         ->select('DISTINCT color')
                         ->where('car_id', $carId)
                         ->order_by('color', 'ASC')
                         ->get_all();
        $colors = [];
        if ($rows) {
            foreach ($rows as $r) {
                if (!empty($r['color'])) $colors[] = $r['color'];
            }
        }
        return $colors;
    }
}
?>