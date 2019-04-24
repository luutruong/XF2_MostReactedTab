<?php

namespace Truonglv\MostReactedTab\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Member extends XFCP_Member
{
    public function actionMrtMostReacted(ParameterBag $params)
    {
        $user = $this->assertViewableUser($params->user_id);

        $tab = $this->filter('tab', 'str');
        if (!in_array($tab, ['thread', 'post'], true)) {
            $tab = 'thread';
        }

        $page = $this->filterPage($params->page);
        $perPage = $this->options()->discussionsPerPage;

        switch ($tab) {
            case 'post':
                $finder = $this->finder('XF:Post');

                $finder->with('full');
                $finder->where('user_id', $user->user_id);
                $finder->where('message_state', 'visible');
                $finder->order('reaction_score', 'desc');

                break;
            default:
                $finder = $this->finder('XF:Thread');

                $finder->with('full');
                $finder->where('user_id', $user->user_id);
                $finder->where('discussion_state', 'visible');
                $finder->order('first_post_reaction_score', 'desc');

                break;
        }

        $total = $finder->total();
        $entities = ($total > 0) ? $finder->limitByPage($page, $perPage)->fetch() : [];

        if ($entities) {
            $entities = $entities->filterViewable();
        }

        return $this->view('Truonglv\MostReactedTab:Member\Tab', 'mrt_most_reacted_content', [
            'page' => $page,
            'perPage' => $perPage,
            'tab' => $tab,
            'user' => $user,
            'total' => $total,
            'entities' => $entities
        ]);
    }
}
