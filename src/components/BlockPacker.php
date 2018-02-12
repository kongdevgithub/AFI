<?php

namespace app\components;

// Growing Bin Packer algorithm for positioning icons in a spritesheet
// using space as efficiently as possible.
//
// This code is a rewritten version of Jake Gordon's GrowingPacker algorithm.
// See <http://codeincomplete.com/posts/2011/5/7/bin_packing/> for more info.
// The algorithm has been adapted from Javascript to PHP and modified lightly.
//

/**
 * BlockPacker
 * @package app\components
 *
 * http://codeincomplete.com/posts/bin-packing/
 * http://pastebin.com/HPQpzxgx
 *
 *
 * For another library see:
 * https://github.com/dvdoug/BoxPacker
 *
 */
class BlockPacker
{
    /**
     * @var
     */
    private $root;

    // Note that $blocks needs to be converted to a StdObject temporarily
    // as arrays cannot be properly passed by reference. This algorithm
    // cannot work in PHP using arrays.

    /**
     * @param $blocks
     * @param null $def_w
     * @param null $def_h
     * @return int
     */
    public function fit($blocks, $def_w = null, $def_h = null)
    {
        $len = count($blocks);
        if (!isset($def_w)) {
            $w = $len > 0 ? $blocks[0]->w : 0;
        } else {
            $w = $def_w;
        }
        if (!isset($def_h)) {
            $h = $len > 0 ? $blocks[0]->h : 0;
        } else {
            $h = $def_h;
        }
        $this->root = (object)array(
            'x' => 0,
            'y' => 0,
            'w' => $w,
            'h' => $h,
            'used' => false,
        );

        foreach ($blocks as &$block) {
            $node = $this->findNode($this->root, $block->w, $block->h);
            if ($node) {
                $block->fit = $this->splitNode($node, $block->w, $block->h);
            } else {
                $block->fit = $this->growNode($block->w, $block->h);
            }
        }

        return $this->root->h;
    }

    /**
     * @param $root
     * @param $w
     * @param $h
     * @return null
     */
    private function findNode(&$root, $w, $h)
    {
        if (isset($root->used) && $root->used) {
            $node = $this->findNode($root->right, $w, $h);
            if ($node) {
                return $node;
            }
            $node = $this->findNode($root->down, $w, $h);
            if ($node) {
                return $node;
            }
        } else if (($w <= $root->w) && ($h <= $root->h)) {
            return $root;
        }
        return null;
    }

    /**
     * @param $node
     * @param $w
     * @param $h
     * @return mixed
     */
    private function splitNode(&$node, $w, $h)
    {
        $node->used = true;
        $node->down = (object)array(
            'x' => $node->x,
            'y' => $node->y + $h,
            'w' => $node->w,
            'h' => $node->h - $h,
        );
        $node->right = (object)array(
            'x' => $node->x + $w,
            'y' => $node->y,
            'w' => $node->w - $w,
            'h' => $h,
        );
        return $node;
    }

    /**
     * @param $w
     * @param $h
     * @return mixed|null
     */
    private function growNode($w, $h)
    {
        $this->root = (object)array(
            'used' => true,
            'x' => 0,
            'y' => 0,
            'w' => $this->root->w,
            'h' => $this->root->h + $h,
            'down' => $this->root,
            'right' => (object)array(
                'x' => 0,
                'y' => $this->root->h,
                'w' => $this->root->w,
                'h' => $h,
            )
        );
        $node = $this->findNode($this->root, $w, $h);
        if ($node) {
            return $this->splitNode($node, $w, $h);
        }
        return null;
    }

}