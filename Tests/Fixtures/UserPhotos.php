<?php declare(strict_types = 1);
/*
 * This file is part of the Bukashk0zzzLiipImagineSerializationBundle
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\LiipImagineSerializationBundle\Tests\Fixtures;

use Bukashk0zzz\LiipImagineSerializationBundle\Annotation as Bukashk0zzz;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * UserPhotos Entity
 *
 * @ORM\Table(name="userPhotos")
 * @ORM\Entity()
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @Vich\Uploadable()
 * @Bukashk0zzz\LiipImagineSerializableClass()
 */
class UserPhotos extends UserPictures
{
    /**
     * @var string Photo name
     *
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\SerializedName("photo")
     *
     * @Bukashk0zzz\LiipImagineSerializableField(filter={"thumb_big", "thumb_small"}, vichUploaderField="photoFile", virtualField="photoThumb")
     */
    public $photoName;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Bukashk0zzz\LiipImagineSerializationBundle\Tests\Fixtures\User", inversedBy="photos")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
}
