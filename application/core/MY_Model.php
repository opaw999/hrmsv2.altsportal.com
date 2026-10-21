<?php

class MY_Model extends CI_Model {

    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    public function set_table($table)
    {
        $this->table = $table;
        return $this;
    }

    // Fluent Query Builder Methods
    public function where($column, $value = null)
    {
        if (is_array($column)) {
            $this->db->where($column);
        } else {
            $this->db->where($column, $value);
        }
        return $this;
    }

    public function whereIn($column, $values)
    {
        $this->db->where_in($column, $values);
        return $this;
    }

    public function orWhere($column, $value = null)
    {
        if (is_array($column)) {
            $this->db->or_where($column);
        } else {
            $this->db->or_where($column, $value);
        }
        return $this;
    }

    public function join($table, $condition, $type = 'inner')
    {
        $this->db->join($table, $condition, $type);
        return $this;
    }

    public function select($columns = '*')
    {
        $this->db->select($columns);
        return $this;
    }

    public function orderBy($column, $direction = 'asc', $escape = NULL)
    {
        $this->db->order_by($column, $direction, $escape);
        return $this;
    }

    public function groupBy($column)
    {
        $this->db->group_by($column);
        return $this;
    }

    public function limit($limit, $offset = 0)
    {
        $this->db->limit($limit, $offset);
        return $this;
    }

    public function like($column, $match, $side = 'both')
    {
        $this->db->like($column, $match, $side);
        return $this;
    }

    public function orLike($column, $match, $side = 'both')
    {
        $this->db->or_like($column, $match, $side);
        return $this;
    }

    // Retrieval
    public function get()
    {
        if (empty($this->db->qb_from)) {
            $this->db->from($this->table);
        }
        
        $query = $this->db->get();
        return $query->result();
    }

    public function first()
    {
        $this->db->limit(1);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    public function find($id)
    {
        $this->db->where($this->primaryKey, $id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    // public function exists($conditions)
    // {
    //     $this->where($conditions);
    //     $query = $this->db->get($this->table);
    //     return $query->num_rows() > 0;
    // }

    public function exists($conditions, $excludeId = null)
    {
        // Use your internal where() to maintain consistency
        $this->where($conditions);

        // If an ID is provided, exclude it from the search
        if ($excludeId !== null) {
            $this->db->where($this->primaryKey . ' !=', $excludeId);
        }
        
        // Use count_all_results to be efficient
        return $this->db->count_all_results($this->table) > 0;
    }
    
    public function count()
    {
        return $this->db->count_all($this->table);
    }

    public function countResults()
    {
        if (empty($this->db->qb_from)) {
            $this->db->from($this->table);
        }

        return $this->db->count_all_results();
    }

    // Create / Update / Delete
    public function create($data)
    {
        $result = $this->db->insert($this->table, $data);
        return $result ? $this->db->insert_id() : false;
    }

    public function update($id, $data)
    {
        $this->db->where($this->primaryKey, $id);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $this->db->where($this->primaryKey, $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    public function deleteWhere($conditions)
    {
        $this->db->where($conditions);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    public function replace($data)
    {
        $result = $this->db->replace($this->table, $data);
        return $result ? $this->db->affected_rows() : false;
    }


    public function __call($method, $args)
    {
        if (method_exists($this->db, $method)) {
            call_user_func_array([$this->db, $method], $args);
            return $this;
        }

        throw new BadMethodCallException("Method {$method} does not exist.");
    }

    public function deleteIn($ids)
    {
        if (empty($ids)) return 0;

        $this->db->where_in($this->primaryKey, $ids);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }
}