<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', 'teacher')->first()
            ?? User::where('role', 'admin')->first()
            ?? User::first();

        if (!$author) {
            return;
        }

        $posts = [
            [
                'title' => 'Helping Children Build a Daily Reading Habit',
                'category' => 'education',
                'image_path' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1400&q=80',
                'excerpt' => 'Small, consistent reading routines can improve vocabulary, confidence, attention span, and classroom performance.',
                'body' => "Reading grows best when it becomes part of a child's normal day. Parents and teachers do not need to begin with long books or difficult passages. Ten to fifteen minutes of focused reading after school, before bedtime, or during a quiet weekend period can make a strong difference.\n\nChildren should be allowed to choose some of what they read. Storybooks, short biographies, age-appropriate magazines, Bible stories, poems, and simple science texts all help them meet language in different forms. The goal is to build curiosity, not pressure.\n\nA useful routine is to ask three gentle questions after reading: What happened? What new word did you notice? What would you do if you were in the story? These questions train comprehension without making reading feel like punishment.\n\nTeachers can support the habit by giving short reading tasks and celebrating effort. Parents can support it by keeping books visible at home and letting children see adults read too. When reading feels natural, children begin to see books as companions in learning.",
            ],
            [
                'title' => 'Why Homework Should Teach Responsibility, Not Fear',
                'category' => 'study tips',
                'image_path' => 'https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=1400&q=80',
                'excerpt' => 'Homework works best when it helps children practise independence and discipline without overwhelming them.',
                'body' => "Homework is most useful when it strengthens what a child has already met in class. It should not become a battle at home or a source of fear. A good homework culture teaches children to plan, attempt, ask questions, and complete tasks with care.\n\nFor younger learners, a fixed homework space can help. The space does not need to be expensive. A clean table, good light, a pencil, and fewer distractions are enough. Children also benefit from a simple routine: eat, rest briefly, then begin homework before play or screen time.\n\nParents should avoid doing the work for the child. Instead, they can read instructions aloud, ask the child to explain the question, and guide them back to class examples. This helps the child learn ownership.\n\nTeachers can make homework more effective by giving clear instructions and balanced tasks. The best homework does not only test memory; it builds responsibility, confidence, and steady study habits.",
            ],
            [
                'title' => 'Building Confidence in Children Who Are Afraid to Answer Questions',
                'category' => 'school life',
                'image_path' => 'https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=1400&q=80',
                'excerpt' => 'Quiet learners often need safety, patience, and small wins before they can speak boldly in class.',
                'body' => "Some children know the answer but remain silent because they fear being wrong. Others have been laughed at before, or they compare themselves with louder classmates. Confidence in the classroom grows when the child feels safe enough to try.\n\nTeachers can begin by asking low-pressure questions. Instead of calling only for one public answer, allow children to think, write, and discuss with a partner first. This gives quiet learners time to organise their thoughts.\n\nPraise should be specific. Saying 'I like how you explained your reason' is more helpful than a general 'good job'. It tells the child what they did well and encourages them to repeat it.\n\nParents can help by allowing children to speak at home without rushing them. Ask about their day, listen fully, and avoid mocking mistakes. A child who feels heard at home is more likely to speak with courage in school.\n\nConfidence is not built in one day. It grows through small moments where a child tries, survives the attempt, and is encouraged to try again.",
            ],
            [
                'title' => 'Preparing Students for Exams Without Panic',
                'category' => 'exams',
                'image_path' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=1400&q=80',
                'excerpt' => 'Calm revision, sleep, practice questions, and honest feedback prepare students better than last-minute panic.',
                'body' => "Exams should measure learning, not destroy peace. Many students panic because they wait until the last week before revising. A better approach is to study in small portions throughout the term.\n\nStudents should keep a simple revision timetable. Each day can focus on one or two subjects, with short breaks between study periods. Practice questions are especially helpful because they show students how topics may appear in exam form.\n\nSleep is part of preparation. A tired child may know the answer but struggle to remember it under pressure. Parents should encourage proper rest, especially during exam week.\n\nTeachers can reduce panic by explaining exam formats early, revising key topics, and giving feedback on common mistakes. Students should know that correction is not shame; it is part of learning.\n\nWhen preparation is steady, exams become less frightening. Children learn that success comes from discipline, not fear.",
            ],
            [
                'title' => 'The Role of Parents in Character Formation',
                'category' => 'parenting',
                'image_path' => 'https://images.unsplash.com/photo-1542810634-71277d95dcbb?auto=format&fit=crop&w=1400&q=80',
                'excerpt' => 'Academic success matters, but honesty, respect, discipline, and kindness shape the whole child.',
                'body' => "Schools can teach values, but parents remain the child's first and most constant teachers. Children observe how adults speak, solve problems, keep promises, handle money, treat workers, and respond to disappointment. These daily examples form character.\n\nA child who sees respect at home learns respect faster. A child who sees adults admit mistakes learns humility. A child who is corrected firmly but lovingly learns discipline without bitterness.\n\nParents should talk about values in practical ways. Honesty is returning extra change. Responsibility is packing your school bag before morning. Kindness is helping a younger child without being forced. These simple lessons become habits when repeated.\n\nAcademic achievement is important, but character carries achievement safely. A brilliant child still needs integrity, patience, courage, and empathy. Home and school should work together to raise children who are not only intelligent, but dependable.",
            ],
            [
                'title' => 'Encouraging Curiosity Through Simple Science at Home',
                'category' => 'education',
                'image_path' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=1400&q=80',
                'excerpt' => 'Children learn deeply when they are allowed to observe, ask questions, and test simple ideas around them.',
                'body' => "Science is not only found in laboratories. It begins when a child asks why rain falls, why shadows move, why plants bend toward light, or why salt disappears in water. These questions should be welcomed because curiosity is the beginning of learning.\n\nParents can encourage science at home with simple observations. Let children plant beans in cotton wool, compare objects that sink or float, watch ice melt, or observe how shadows change during the day. The important part is not a perfect experiment; it is careful noticing.\n\nTeachers can connect classroom science to daily life. When children see science in cooking, farming, weather, health, and technology, the subject becomes meaningful.\n\nA curious child becomes an active learner. They do not only wait for answers; they learn to investigate. That spirit is valuable in every subject, not science alone.",
            ],
        ];

        foreach ($posts as $index => $post) {
            BlogPost::updateOrCreate(
                ['slug' => Str::slug($post['title'])],
                [
                    'author_id' => $author->id,
                    'reviewed_by' => $author->id,
                    'title' => $post['title'],
                    'category' => $post['category'],
                    'image_path' => $post['image_path'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'status' => BlogPost::STATUS_PUBLISHED,
                    'submitted_at' => now()->subDays(10 - $index),
                    'published_at' => now()->subDays(6 - $index),
                ]
            );
        }
    }
}
