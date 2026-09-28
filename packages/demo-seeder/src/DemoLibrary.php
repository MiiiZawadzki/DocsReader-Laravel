<?php

namespace DocsReader\DemoSeeder;

/**
 * The content the demo seeder plants: a fictional company, its staff, and the
 * mandatory reading they have been issued.
 */
final class DemoLibrary
{
    public const EMAIL_DOMAIN = 'demo.docsreader.test';

    public const PERSONA_DILIGENT = 'diligent';

    public const PERSONA_SKIMMER = 'skimmer';

    public const PERSONA_GHOST = 'ghost';

    public const PERSONA_PARTIAL = 'partial';

    public const PERSONA_LAST_MINUTE = 'last-minute';

    public const PERSONA_OBSESSIVE = 'obsessive';

    public const PERSONA_RETURNER = 'returner';

    /**
     * Exactly one person manages: they own every document and carry the
     * manage-documents permission. Everyone else is assigned documents only.
     *
     * @return array<int, array{name: string, email: string, title: string, manages: bool, persona: string}>
     */
    public static function people(): array
    {
        return [
            [
                'name' => 'Margaret Pyle',
                'email' => 'margaret.pyle',
                'title' => 'Head of Compliance. Owns every document here, and wrote most of them',
                'manages' => true,
                'persona' => self::PERSONA_DILIGENT,
            ],
            [
                'name' => 'Dev Ramanathan',
                'email' => 'dev.ramanathan',
                'title' => 'Facilities Lead. Gets there across several sittings',
                'manages' => false,
                'persona' => self::PERSONA_RETURNER,
            ],
            [
                'name' => 'Aoife Brennan',
                'email' => 'aoife.brennan',
                'title' => 'Reads every page. Twice. Confirms the same afternoon',
                'manages' => false,
                'persona' => self::PERSONA_DILIGENT,
            ],
            [
                'name' => 'Tomasz Wierzbicki',
                'email' => 'tomasz.wierzbicki',
                'title' => 'Reads properly, no drama',
                'manages' => false,
                'persona' => self::PERSONA_DILIGENT,
            ],
            [
                'name' => 'Callum Fenwick',
                'email' => 'callum.fenwick',
                'title' => 'Pages to the end in nine seconds, then wonders why the button is grey',
                'manages' => false,
                'persona' => self::PERSONA_SKIMMER,
            ],
            [
                'name' => 'Priya Nandakumar',
                'email' => 'priya.nandakumar',
                'title' => 'Also a skimmer, but at least opens it',
                'manages' => false,
                'persona' => self::PERSONA_SKIMMER,
            ],
            [
                'name' => 'Gareth Oyelaran',
                'email' => 'gareth.oyelaran',
                'title' => 'Has never opened the app. Assigned everything anyway',
                'manages' => false,
                'persona' => self::PERSONA_GHOST,
            ],
            [
                'name' => 'Ingrid Solberg',
                'email' => 'ingrid.solberg',
                'title' => 'Stops halfway through everything, forever',
                'manages' => false,
                'persona' => self::PERSONA_PARTIAL,
            ],
            [
                'name' => 'Bartosz Zielinski',
                'email' => 'bartosz.zielinski',
                'title' => 'Confirms everything on the final day of the window',
                'manages' => false,
                'persona' => self::PERSONA_LAST_MINUTE,
            ],
            [
                'name' => 'Hester Vandermolen',
                'email' => 'hester.vandermolen',
                'title' => 'Left page one open for two hours. Read nothing else',
                'manages' => false,
                'persona' => self::PERSONA_OBSESSIVE,
            ],
            [
                'name' => 'Femi Adeyemi',
                'email' => 'femi.adeyemi',
                'title' => 'Three sessions across three days, gets there in the end',
                'manages' => false,
                'persona' => self::PERSONA_RETURNER,
            ],
            [
                'name' => 'Nerys Caradoc',
                'email' => 'nerys.caradoc',
                'title' => 'Reliable. Boring. The control group',
                'manages' => false,
                'persona' => self::PERSONA_PARTIAL,
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     title: string,
     *     description: string,
     *     declaration: string|null,
     *     requiresConfirmation: bool,
     *     delay: int,
     *     dateFrom: string,
     *     dateTo: string|null,
     *     pages: array<int, array{heading: string, body: array<int, string>}>
     * }>
     */
    public static function documents(): array
    {
        return [
            [
                'key' => 'microwave',
                'title' => 'Kitchen Microwave Usage Policy (Rev. 7)',
                'description' => 'Supersedes Rev. 6 in its entirety. Rev. 6 is not to be discussed.',
                'declaration' => 'I confirm I have read the Microwave Usage Policy and accept that fish is a matter of public record.',
                'requiresConfirmation' => true,
                'delay' => 8,
                'dateFrom' => '-45 days',
                'dateTo' => '+120 days',
                'pages' => [
                    [
                        'heading' => '1. Purpose and Scope',
                        'body' => [
                            'This policy establishes a consistent framework for the operation of the shared microwave located in the second floor kitchen, hereafter "the Appliance". It applies to all staff, contractors, and any visitor who has been left unattended near it.',
                            'Rev. 7 was issued following a review of the incident log for the preceding quarter. That log now runs to fourteen pages and has been excluded from this document on the grounds of length and morale.',
                            'Nothing in this policy should be read as an accusation. The Compliance function wishes to state plainly that it does not know whose salmon it was, and has stopped trying to find out.',
                        ],
                    ],
                    [
                        'heading' => '2. Prohibited Items',
                        'body' => [
                            'The following may not be heated in the Appliance under any circumstances: fish of any description; any dish the owner describes as "leftover curry, but it is fine"; popcorn beyond two minutes fifteen seconds; eggs in any configuration, including configurations not yet attempted.',
                            'Staff are reminded that the phrase "it only smells for a minute" has been formally assessed as inaccurate. Measurements taken in the stairwell suggest a persistence of approximately forty minutes, or one full afternoon on humid days.',
                            'A request to add "anything reheated for a third time" to this list was tabled and deferred pending a definition of "third".',
                        ],
                    ],
                    [
                        'heading' => '3. The Odour Escalation Matrix',
                        'body' => [
                            'Tier 1 - Noticeable at the Appliance. No action required. Open the window if it is not raining.',
                            'Tier 2 - Noticeable in the corridor. The individual responsible should return to the kitchen and address it. No report is filed.',
                            'Tier 3 - Noticeable at reception. Facilities are notified. A note is placed on the Appliance for the remainder of the day.',
                            'Tier 4 - Noticeable from the car park. Facilities, Compliance, and the building manager are notified simultaneously. The kitchen is closed for ninety minutes. This has occurred once.',
                            'There is no Tier 5. Do not attempt to establish one.',
                        ],
                    ],
                    [
                        'heading' => '4. Amnesty and Closing Provisions',
                        'body' => [
                            'A general amnesty is declared in respect of all events occurring before the issue date of this revision. No further enquiries will be made, no records retained, and the matter is considered closed by all parties including the building manager, who has agreed in writing to stop mentioning it.',
                            'This policy will be reviewed annually, or immediately following any Tier 4 event, whichever comes first.',
                            'Queries should be directed to Facilities. Please do not direct them to Compliance, which has asked to be left out of this going forward.',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'terrence',
                'title' => 'Acceptable Use of the Office Plant',
                'description' => 'Concerning Terrence, the ficus in the north-east corner.',
                'declaration' => 'I acknowledge that Terrence is not a bin, a coat stand, or a participant in meetings.',
                'requiresConfirmation' => true,
                'delay' => 6,
                'dateFrom' => '-30 days',
                'dateTo' => null,
                'pages' => [
                    [
                        'heading' => '1. Background',
                        'body' => [
                            'Terrence is a Ficus benjamina acquired in 2019 as part of a wellbeing initiative that has otherwise been discontinued. He is the last remaining asset of that programme and is carried on the fixed asset register at a nominal value of one unit of currency.',
                            'Terrence has outlived two office relocations, one rebrand, and the wellbeing initiative itself. Facilities consider this material to his standing within the organisation.',
                        ],
                    ],
                    [
                        'heading' => '2. Permitted and Prohibited Uses',
                        'body' => [
                            'Permitted: looking at Terrence; watering Terrence in accordance with the rota; quiet appreciation of Terrence.',
                            'Prohibited: depositing cups, wrappers, or cores in his pot; hanging garments from his branches; relocating him to improve a video call background; introducing a second plant into his corner without consultation; addressing Terrence during standup.',
                            'The pot is not a bin. This has been stated in three previous communications and is restated here for completeness. Six cups were recovered in the last audit, two of them still full.',
                        ],
                    ],
                    [
                        'heading' => '3. The Watering Rota',
                        'body' => [
                            'Terrence is watered on Tuesdays. He requires approximately 300ml. He does not require 300ml from four separate people, which is what occurred in March and which Facilities describe as the wettest week of his life.',
                            'The rota is maintained by Facilities and posted beside the Appliance referred to in the Kitchen Microwave Usage Policy. Staff on the rota who are absent should arrange cover rather than assume someone will notice.',
                            'Overwatering is the leading cause of decline in indoor ficus. Enthusiasm is not a defence.',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'meeting-rooms',
                'title' => 'Meeting Room Booking Etiquette, 4th Edition',
                'description' => 'Includes the ghost booking amnesty and the revised Ludlow provisions.',
                'declaration' => 'I confirm I will release rooms I am not using, including the good one.',
                'requiresConfirmation' => true,
                'delay' => 10,
                'dateFrom' => '-60 days',
                'dateTo' => '+60 days',
                'pages' => [
                    [
                        'heading' => '1. The Rooms',
                        'body' => [
                            'The organisation operates four bookable rooms: Ludlow (eight seats, the good one), Ashby (six seats, warm), Crayle (four seats, no window), and Bramber (two seats, formerly a cupboard and still shaped like one).',
                            'Ludlow is booked 94% of available hours. Attendance data suggests it is actually occupied for 51% of those hours. This document exists principally because of that gap.',
                        ],
                    ],
                    [
                        'heading' => '2. Ghost Bookings',
                        'body' => [
                            'A ghost booking is a reservation held but not used, whether through cancellation, forgetfulness, or the practice of holding Ludlow every Thursday "just in case" for a meeting that has not convened since the spring.',
                            'An amnesty applies to all recurring bookings created before this edition. They have been cleared. If your meeting was real, please book it again. Several did not.',
                            'A room unoccupied ten minutes after the start of a booking may be claimed by anyone. Staff are asked to claim politely and not to make a performance of it.',
                        ],
                    ],
                    [
                        'heading' => '3. The Ludlow Provisions',
                        'body' => [
                            'Ludlow may not be booked for meetings of fewer than four people. Ludlow may not be booked for a call that one person will take alone with the door shut. Bramber exists for this purpose and has been sincerely described by Facilities as "perfectly adequate".',
                            'Ludlow may not be booked back-to-back for the full day by a single team, a practice referred to internally as "the siege" and now formally discouraged.',
                            'Requests to install a second screen in Ludlow are noted, have been noted since 2023, and remain noted.',
                        ],
                    ],
                    [
                        'heading' => '4. Bramber',
                        'body' => [
                            'Bramber seats two people at a measured distance of 60cm. It has no ventilation and one power socket which is behind the chair.',
                            'Facilities acknowledge that Bramber is not popular. Facilities would like to note that it is always available, and invite staff to consider what that tells them.',
                            'Bramber was a cupboard. It is now a room. Nothing about it has changed except the sign.',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'printer',
                'title' => 'Printer Jam Escalation Pathway',
                'description' => 'What to do, in order, and what not to do first.',
                'declaration' => 'I will not open Tray 4.',
                'requiresConfirmation' => true,
                'delay' => 5,
                'dateFrom' => '-20 days',
                'dateTo' => null,
                'pages' => [
                    [
                        'heading' => '1. First Response',
                        'body' => [
                            'On encountering a jam, read the display. The display is usually correct. In the majority of logged incidents the display named the affected tray and was overruled by the member of staff present.',
                            'Do not open every tray. Opening every tray resets the sensor sequence and the device will report a jam in a tray that is empty and has always been empty.',
                            'Do not pull paper against the direction of travel. Arrows are printed inside the unit for this purpose and are, by universal agreement, too small.',
                        ],
                    ],
                    [
                        'heading' => '2. Tray 4',
                        'body' => [
                            'Tray 4 does not contain paper. Tray 4 has never contained paper. Tray 4 is a drawer that was included with the unit and serves no function in our configuration.',
                            'Tray 4 is nonetheless opened first in an estimated 70% of incidents, which Facilities attribute to it being at a comfortable height.',
                            'Please leave Tray 4 closed.',
                        ],
                    ],
                    [
                        'heading' => '3. Escalation',
                        'body' => [
                            'If the jam persists after one attempt following the display, stop. Do not attempt a second removal. Log a ticket and leave a note on the unit so that the next person does not begin the cycle again.',
                            'Three sequential unsuccessful attempts by three different people is the most common precursor to a service call, and the service engineer has asked us to mention it.',
                            'The engineer has also asked us to mention Tray 4.',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'fridge',
                'title' => 'Refrigerator Amnesty and Tupperware Retention Schedule',
                'description' => 'Effective immediately. Applies retrospectively to nothing.',
                'declaration' => 'I accept that unlabelled containers are surrendered on Friday.',
                'requiresConfirmation' => true,
                'delay' => 7,
                'dateFrom' => '-14 days',
                'dateTo' => '+30 days',
                'pages' => [
                    [
                        'heading' => '1. Retention Periods',
                        'body' => [
                            'Labelled containers are retained until the date written on the label, or seven days, whichever is sooner. A label consists of a name and a date. A label consisting only of the word "MINE" is not a label.',
                            'Unlabelled containers are retained until the Friday clearance and are then surrendered. Surrendered containers are held for two weeks in the box beneath the sink and thereafter disposed of.',
                            'The box beneath the sink currently holds nineteen containers. Four have lids. None have been claimed since January.',
                        ],
                    ],
                    [
                        'heading' => '2. The Friday Clearance',
                        'body' => [
                            'Clearance occurs at 16:00 each Friday. Notice is not given. Notice was given for the first six weeks and was found to produce a burst of retrospective labelling at 15:55 rather than any change in behaviour.',
                            'Items in the door are included in the clearance. The door is part of the refrigerator. This was disputed and has been resolved.',
                            'One shelf is designated for items belonging to the office rather than to individuals. That shelf is also cleared, because it has become the place people put things they do not want cleared.',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'mandatory-fun',
                'title' => 'Team Building Participation Framework',
                'description' => 'Participation is voluntary. Attendance is recorded.',
                'declaration' => 'I have read the Participation Framework and understand what voluntary means here.',
                'requiresConfirmation' => true,
                'delay' => 30,
                'dateFrom' => '-25 days',
                'dateTo' => '+45 days',
                'pages' => [
                    [
                        'heading' => '1. Principles',
                        'body' => [
                            'Team building activities are voluntary. The organisation wishes to be unambiguous on this point, having been asked about it repeatedly and at volume.',
                            'Attendance is recorded for catering and room capacity purposes. Records are retained for twelve months. Records are not used in performance discussions, a sentence which has itself been the subject of two performance discussions.',
                            'Staff who do not wish to attend need not provide a reason. Staff who provide a reason will find it accepted without examination, including the reason given in April which Compliance has chosen not to reproduce here.',
                        ],
                    ],
                    [
                        'heading' => '2. Activity Selection',
                        'body' => [
                            'Activities are proposed by the Social Committee and selected by anonymous vote. The vote is genuinely anonymous. The escape room has now won four consecutive votes, which the Committee describes as a mandate and others describe as a small number of people voting repeatedly.',
                            'Activities involving heights, water, public singing, or the phrase "trust exercise" require an opt-in rather than an opt-out.',
                            'The Committee has been asked to stop proposing karaoke. The Committee has noted this.',
                        ],
                    ],
                    [
                        'heading' => '3. Catering',
                        'body' => [
                            'Dietary requirements must be submitted 72 hours in advance. Requirements submitted later will be accommodated where possible, which in practice means a jacket potato.',
                            'The jacket potato is not a punishment. It is what is available at short notice. Four people have now asked.',
                            'Please note that catering for these events is prepared off site and does not involve the Appliance. This has been confirmed in writing.',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'cables',
                'title' => 'Cable Management Compliance Standard',
                'description' => 'Reference document. No confirmation required.',
                'declaration' => null,
                'requiresConfirmation' => false,
                'delay' => 4,
                'dateFrom' => '-90 days',
                'dateTo' => null,
                'pages' => [
                    [
                        'heading' => '1. Standard',
                        'body' => [
                            'Cables under desks should be secured to the tray provided. Cables crossing a walkway must be covered. Cables described by their owner as "temporary" for more than one calendar month are no longer temporary and fall within this standard.',
                            'The desk in the north-west corner currently carries eleven cables serving one monitor. An audit is scheduled. The occupant has been informed and has begun what they describe as "a rationalisation".',
                        ],
                    ],
                    [
                        'heading' => '2. Adapters',
                        'body' => [
                            'The pool of shared adapters is held in the second drawer of the storage unit, not in individual desk drawers, not in bags, and not at home.',
                            'Fourteen adapters were purchased. Five are in the drawer. The standard makes no provision for the other nine, and Facilities have stopped counting.',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'descaling',
                'title' => 'Coffee Machine Descaling Protocol',
                'description' => 'Annex C addresses the beeping. Effective from next month.',
                'declaration' => 'I understand the beeping and will not unplug the machine to stop it.',
                'requiresConfirmation' => true,
                'delay' => 9,
                'dateFrom' => '+14 days',
                'dateTo' => '+200 days',
                'pages' => [
                    [
                        'heading' => '1. Cycle',
                        'body' => [
                            'The machine requires descaling every 400 cycles or monthly, whichever comes first. At current consumption this is every nine days. The manufacturer was consulted and expressed surprise.',
                            'Descaling takes 25 minutes and cannot be interrupted. Interrupting it produces the condition described in Annex C.',
                        ],
                    ],
                    [
                        'heading' => '2. Annex C: The Beeping',
                        'body' => [
                            'On interruption the machine enters a state in which it beeps every 90 seconds indefinitely. The beep is not documented in the manual. The manufacturer has confirmed the beep exists and declined to elaborate.',
                            'The beep is cleared by completing a full descale cycle from the beginning. It is not cleared by unplugging the machine, which restarts the beep on reconnection, nor by pressing all four buttons simultaneously, which was attempted in July and produced a second, different beep.',
                            'Staff are asked not to investigate the second beep.',
                        ],
                    ],
                    [
                        'heading' => '3. Responsibility',
                        'body' => [
                            'The person who begins a descale cycle owns that cycle to completion. Beginning a cycle at 17:25 and leaving is the single most common cause of an Annex C event.',
                            'A sign-up sheet is posted beside the machine. It has one name on it. That name has been on it since the sheet was introduced.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
