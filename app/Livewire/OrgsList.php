<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\Technology;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OrgsList extends Component
{
    public function __construct(public $filterTechnology = null)
    {
        // @todo: Change navigation to filter/not filter technology to be on the same livewire page, even with URL changing
    }

    #[Computed(cache: true, key: 'active-technologies')]
    public function technologies()
    {
        return Technology::whereHas('organizations')->orderBy('name')->get();
    }

    #[Computed]
    public function organizations()
    {
        return Cache::remember('orgs-list-filter[' . $this->filterTechnology . ']', 3600, function () {
            return Organization::when(! is_null($this->filterTechnology), function (Builder $query) {
                $query->whereHas('technologies', function (Builder $query) {
                    $query->where('slug', $this->filterTechnology);
                });
            })->with('sites') // @todo: Do a subquery for just the first site aaron francis style?
                ->orderBy('featured_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public function render()
    {
        return view('livewire.orgs-list');
    }
}
