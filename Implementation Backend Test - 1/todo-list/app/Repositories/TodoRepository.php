<?php

namespace App\Repositories;

use App\Models\Todo;

class TodoRepository
{
    public function all()
    {
        return Todo::all();
    }

    public function create(array $data)
    {
        return Todo::create($data);
    }

    public function find($id)
    {
        return Todo::findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $todo = $this->find($id);
        $todo->update($data);
        return $todo;
    }

    public function delete($id)
    {
        $todo = $this->find($id);
        $todo->delete();
    }
}
