<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Novel;
use App\Models\NovelPromotion;
use App\Models\Tag;
use App\Models\Setting;
use App\Services\GeoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class Catalog extends Component {
    use WithPagination;

    public $search = '';
    public $selectedTags = [];
    public $status = '';
    public $sort = 'latest';
    public $showFilters = false;
    
    public $year = '';
    public $author = '';
    public $country = '';

    protected $queryString = ['search', 'selectedTags', 'status', 'sort', 'year', 'author', 'country'];

    public function mount() {
        if (request()->has('tags')) {
            $this->selectedTags = explode(',', request('tags'));
        }
    }

    public function toggleFilters() { $this->showFilters = !$this->showFilters; }
    public function resetFilters() {
        $this->search = '';
        $this->selectedTags = [];
        $this->status = '';
        $this->year = '';
        $this->author = '';
        $this->country = '';
        $this->resetPage();
    }
    public function toggleTag($tagId) {
        if (in_array($tagId, $this->selectedTags)) {
            $this->selectedTags = array_diff($this->selectedTags, [$tagId]);
        } else {
            $this->selectedTags[] = $tagId;
        }
        $this->resetPage();
    }

    public function setStatus(string $val) {
        $this->status = $this->status === $val ? '' : $val;
        $this->resetPage();
    }

    public function updated($propertyName) { $this->resetPage(); }

    public function render() {
        $query = Novel::where('is_published', true);

        if (GeoService::isRu(Request::ip())) {
            $query->where('is_restricted', false);
        }

        if (!Auth::check()) {
            $query->where('hide_from_guests', false);
            if (filter_var(Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN)) {
                $query->where('is_adult', false);
            }
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('author_name', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedTags)) {
            $query->whereHas('tags', function($q) {
                $q->whereIn('tags.id', $this->selectedTags);
            });
        }

        if ($this->status) $query->where('status', $this->status);
        if ($this->year) $query->whereYear('created_at', $this->year);
        if ($this->author) $query->where('author_name', 'like', '%' . $this->author . '%');

        switch ($this->sort) {
            case 'popular': $query->orderBy('views', 'desc'); break;
            case 'rating': 
                $query->withCount(['ratings as average_rating' => function($q) {
                    $q->select(\DB::raw('coalesce(avg(score),0)'));
                }])->orderBy('average_rating', 'desc'); 
                break;
            default: $query->latest(); break;
        }

        $promotedIds = NovelPromotion::active()
            ->where('type', 'catalog_top')
            ->pluck('novel_id');

        $promotedNovels = $promotedIds->isNotEmpty()
            ? Novel::whereIn('id', $promotedIds)->where('is_published', true)->withCount('chapters')->get()
            : collect();

        return view('livewire.catalog', [
            'novels' => $query->withCount('chapters')->paginate(12),
            'tags' => Tag::all(),
            'promotedNovels' => $promotedNovels,
        ])->layout('layouts.app')->title('Каталог');
    }
}
