<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;
    protected $table = 'dosen';

    protected $fillable = [
        'user_id', 
        'gambar',
        'nama', 
        'nidn', 
        'departemen', 
        'no_hp', 
        'alamat', 
        'deskripsi'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function dosenPembimbings()
{
    return $this->hasMany(DosenPembimbing::class);
}

public function seminarProposals1()
    {
        return $this->hasMany(SeminarProposal::class, 'dosen_penguji_1_id');
    }

    public function seminarProposals2()
    {
        return $this->hasMany(SeminarProposal::class, 'dosen_penguji_2_id');
    }

}
