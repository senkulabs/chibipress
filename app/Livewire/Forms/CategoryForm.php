<?php

namespace App\Livewire\Forms;

use App\Models\Category;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Validation\Rule;

class CategoryForm extends Form
{
    public ?Category $category;

    #[Validate]
    public $name = '';
    public $slug = '';

    public function setCategory(Category $category)
    {
        $this->category = $category;
        $this->name = $category->name;
        $this->slug = $category->slug;
    }

    protected function rules()
    {
        return [
            'name' => [
                'required',
                Rule::unique('categories')->ignore($this->category)
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', // Only lowercase letters, numbers, and hypens,
                Rule::unique('categories')->ignore($this->category),
            ]
        ];
    }

    protected function messages()
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens, and cannot start or end with a hyphen.'
        ];
    }

    public function store()
    {
        $this->validate();

        Category::create($this->only(['name', 'slug']));

        $this->reset();

        $this->category = new Category();
    }

    public function update()
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->slug = $this->category->generateUniqueSlug($this->name);
        }

        $this->category->update($this->only(['name', 'slug']));
    }
}
