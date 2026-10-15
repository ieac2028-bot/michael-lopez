<?php

namespace Database\Seeders;

use App\Models\Attorney;
use App\Models\CaseRecord;
use App\Models\PracticeArea;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AbogadokonekSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['Intellectual Property', 'The Firm is one of the pioneering and leading practitioners in the field of intellectual property, including trademarks, patents, copyrights, licensing, franchising, and technology transfer. It has an international network of correspondents in almost every country of the world. Services include trademark and service marks; patents, industrial design and utility models; copyrights and unfair competition; and consultation and due diligence.'],
            ['Commercial Law', 'The Firm acts as legal adviser for companies on their investments in the Philippines. This role involves drafting and negotiating contracts; assisting companies with corporate formation and registration, corporate governance, corporate finance, joint ventures, mergers and acquisitions, transfers of shares, and shareholders’ agreements; and providing legal services in commercial administration, taxation, labor issues, and other corporate transactions.'],
            ['Investments', 'The Firm provides end-to-end legal support for local and foreign investments in the Philippines, helping clients structure and implement business ventures in compliance with applicable laws and regulations. It advises on investment vehicles, ownership structures, and applicable restrictions under the Foreign Investments Act, Retail Trade Liberalization Act, and other relevant statutes. Services include business registration, licenses and permits, joint venture and shareholder agreements, and regulatory compliance with agencies such as the SEC, DTI, BOI, and PEZA. The Firm also helps clients manage legal risks involving taxation, labor, land acquisition, and corporate governance.'],
            ['Litigation', 'The Firm represents clients in civil, commercial, and administrative disputes before trial courts, appellate courts, and quasi-judicial bodies in the Philippines. Matters include breach of contract, torts, property disputes, debt recovery, corporate conflicts, and regulatory issues. Its approach combines strategic planning, legal analysis, and courtroom advocacy, as well as pre-litigation advice, risk assessment, and alternative dispute resolution such as mediation and arbitration when appropriate.'],
            ['Labor Law', 'The Firm prosecutes or defends labor cases before the Department of Labor and Employment, the Court of Appeals, and the Supreme Court. It also advises corporate management on Philippine labor laws to help avoid complications in their interpretation and application to business operations.'],
            ['Family Relations and Estate Planning', 'The Firm offers legal support in family relations and estate planning. Attorneys address matters such as marriage, divorce, child custody, and adoption, focusing on clients and their families, and are prepared to advocate in court when needed. Estate-planning services include tailored plans for asset distribution and care for loved ones, including wills and trusts, with guidance on available options and legal processes.'],
            ['Property', 'The Firm provides comprehensive legal services in Philippine property law, guiding clients through real estate transactions, land and property acquisition, title verification, due diligence, contract drafting, and registration with relevant government agencies. It assists with residential, commercial, or agricultural property matters, zoning and land-use compliance, property disputes, boundary claims, and enforcement of property rights.'],
            ['Maritime Law', 'The Firm provides legal services to clients in the shipping and maritime industry in the Philippines. Matters include vessel registration, documentation, and compliance with regulations administered by agencies such as MARINA and the Philippine Coast Guard; charter parties, bills of lading, cargo claims, marine insurance, collisions, salvage, and maritime labor; and disputes before courts and administrative bodies, including damages claims, maritime liens, and ship-arrest proceedings. The Firm advises on international maritime conventions and domestic laws.'],
            ['Taxation', 'The Firm provides strategic advice on Philippine tax law, compliance, and tax efficiency for individuals, businesses, and corporations. Services include tax planning, transaction structuring, income, value-added, and withholding tax advice, tax filings, and assistance with audits and investigations by the Bureau of Internal Revenue and local government units. The Firm also represents clients in assessments, protests, refund claims, and appeals before administrative and judicial bodies.'],
            ['Immigration Law', 'The Firm assists individuals and businesses with immigration matters, including visa applications, residency permits, and citizenship applications. It guides clients through requirements and deadlines and represents them in immigration hearings and appeals before immigration authorities.'],
            ['Special Projects', 'The Firm provides legal guidance for innovative initiatives and unique business opportunities, including new ventures and public-private partnerships, helping clients navigate their legal requirements and pursue their objectives.'],
        ];

        foreach ($areas as $i => [$name, $description]) {
            PracticeArea::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description, 'sort_order' => $i]
            );
        }

        $ip = PracticeArea::where('slug', 'intellectual-property')->first();
        $labor = PracticeArea::where('slug', 'labor-law')->first();
        $tax = PracticeArea::where('slug', 'taxation')->first();
        $commercial = PracticeArea::where('slug', 'commercial-law')->first();

        // Fictional sample profiles for the student project demo; not real attorneys.
        $attorneys = [
            ['Atty. Amara Reyes', 'Senior Partner', $ip?->id, 'Trademarks, patents, copyright, and licensing', 4.9, '2026-09-18', 8, 0],
            ['Atty. Gabriel Santos', 'Senior Partner', $commercial?->id, 'Business formation, contracts, and corporate governance', 4.8, '2026-09-12', 12, 1],
            ['Atty. Sofia Mendoza', 'Senior Associate', $labor?->id, 'Workplace disputes, labor standards, and employment matters', 4.9, '2026-08-29', 7, 2],
            ['Atty. Rafael Cruz', 'Senior Associate', $tax?->id, 'Tax compliance, assessments, and administrative appeals', 4.7, '2026-09-05', 10, 3],
            ['Atty. Bianca Navarro', 'Associate', $ip?->id, 'Trademark searches, copyright, and technology transfer', 4.8, '2026-08-21', 5, 4],
            ['Atty. Mateo Garcia', 'Associate', $commercial?->id, 'Commercial agreements, investments, and company matters', 4.6, '2026-09-02', 6, 5],
            ['Atty. Elena Torres', 'Senior Associate', PracticeArea::where('slug', 'family-relations-and-estate-planning')->value('id'), 'Family relations, estate planning, and succession', 4.9, '2026-07-24', 9, 6],
            ['Atty. Joaquin Flores', 'Associate', PracticeArea::where('slug', 'property')->value('id'), 'Property transactions, land titles, and property disputes', 4.7, '2026-08-14', 4, 7],
            ['Atty. Camille Aquino', 'Associate', PracticeArea::where('slug', 'immigration-law')->value('id'), 'Visa applications, residency, and immigration appeals', 4.8, '2026-09-09', 5, 8],
            ['Atty. Nicolas Dela Cruz', 'Legal Staff', PracticeArea::where('slug', 'litigation')->value('id'), 'Civil and commercial litigation support', 4.6, '2026-08-06', 3, 9],
            ['Sample Attorney 11', 'Senior Associate', $ip?->id, 'Patent applications, licensing, and intellectual property disputes', 4.7, '2026-09-22', 9, 10],
            ['Sample Attorney 12', 'Associate', $commercial?->id, 'Commercial contracts, corporate governance, and business transactions', 4.5, '2026-09-19', 4, 11],
            ['Sample Attorney 13', 'Senior Associate', PracticeArea::where('slug', 'investments')->value('id'), 'Investment structures, regulatory filings, and joint ventures', 4.8, '2026-09-16', 8, 12],
            ['Sample Attorney 14', 'Senior Partner', PracticeArea::where('slug', 'litigation')->value('id'), 'Civil disputes, mediation, and appellate litigation', 4.9, '2026-09-25', 16, 13],
            ['Sample Attorney 15', 'Associate', $labor?->id, 'Employment contracts, labor standards, and workplace disputes', 4.6, '2026-09-11', 5, 14],
            ['Sample Attorney 16', 'Senior Associate', PracticeArea::where('slug', 'family-relations-and-estate-planning')->value('id'), 'Estate planning, succession, and family matters', 4.7, '2026-09-08', 10, 15],
            ['Sample Attorney 17', 'Associate', PracticeArea::where('slug', 'property')->value('id'), 'Land title review, leases, and property transactions', 4.6, '2026-09-14', 6, 16],
            ['Sample Attorney 18', 'Senior Associate', PracticeArea::where('slug', 'maritime-law')->value('id'), 'Shipping documentation, cargo claims, and maritime disputes', 4.8, '2026-09-20', 11, 17],
            ['Sample Attorney 19', 'Associate', $tax?->id, 'Tax compliance, BIR assessments, and refund claims', 4.5, '2026-09-06', 5, 18],
            ['Sample Attorney 20', 'Senior Associate', PracticeArea::where('slug', 'immigration-law')->value('id'), 'Visa applications, residency matters, and immigration appeals', 4.7, '2026-09-17', 9, 19],
            ['Sample Attorney 21', 'Associate', PracticeArea::where('slug', 'special-projects')->value('id'), 'Public-private partnerships and emerging business projects', 4.6, '2026-09-10', 6, 20],
            ['Sample Attorney 22', 'Associate', $ip?->id, 'Trademark registration, copyright, and technology transfer', 4.8, '2026-09-23', 5, 21],
            ['Sample Attorney 23', 'Senior Associate', $commercial?->id, 'Mergers, shareholder agreements, and corporate transactions', 4.7, '2026-09-21', 12, 22],
            ['Sample Attorney 24', 'Associate', PracticeArea::where('slug', 'litigation')->value('id'), 'Commercial disputes, contract claims, and case preparation', 4.5, '2026-09-13', 4, 23],
            ['Sample Attorney 25', 'Senior Associate', PracticeArea::where('slug', 'investments')->value('id'), 'Foreign investment registration and regulatory compliance', 4.8, '2026-09-24', 10, 24],
        ];

        $attorneyIds = [];
        $legacyNames = array_map(fn ($number) => sprintf('Sample Attorney %02d', $number), range(1, 6));
        foreach ($attorneys as [$name, $rank, $areaId, $note, $rating, $lastPracticedOn, $years, $order]) {
            if ($order < count($legacyNames)) {
                Attorney::where('name', $legacyNames[$order])->update(['name' => $name]);
            }

            $photoPath = 'images/attorneys/attorney-' . ($order + 1) . '.svg';
            $attorneyIds[] = Attorney::updateOrCreate(
                ['name' => $name],
                [
                    'photo_path' => $photoPath,
                    'rank' => $rank,
                    'practice_area_id' => $areaId,
                    'specialization_note' => $note,
                    'sample_rating' => $rating,
                    'sample_last_practiced_on' => $lastPracticedOn,
                    'years_experience' => $years,
                    'sort_order' => $order,
                ]
            )->id;
        }

        CaseRecord::updateOrCreate(
            ['reference_no' => 'ABG-DEMO-0001'],
            [
                'client_email' => 'demo@client.com',
                'title' => 'Sample consultation request',
                'attorney_id' => $attorneyIds[4] ?? null,
                'practice_area_id' => $ip?->id,
                'status' => 'active',
                'submitted_at' => '2026-09-12 09:00:00',
                'accepted_at' => '2026-09-15 14:30:00',
                'timeline' => [
                    ['label' => 'Sample request submitted', 'date' => '2026-09-12', 'state' => 'done'],
                    ['label' => 'Referred to an attorney', 'date' => '2026-09-15', 'state' => 'done'],
                    ['label' => 'Case review in progress', 'date' => null, 'state' => 'current'],
                    ['label' => 'Next update from the attorney', 'date' => null, 'state' => 'pending'],
                ],
            ]
        );
    }
}
