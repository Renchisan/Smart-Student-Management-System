<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
const page = usePage();
import { ref, computed, watch } from "vue";

const user = page.props.auth.user.name;
const student = page.props.student;

const programName = computed(() => {
    if (student.program === 'CS') return 'Computer Science';
    if (student.program === 'IT') return 'Information Technology';
    return student.program; // fallback if it's neither
});

function convertGrade(g) {
    if (g < 0) g = 0;
    if (g > 19) g = 19;
    const converted = 5 - (g / 19) * 4; // maps 19->1 and 0->5
    const rounded = Math.round(converted * 4) / 4; // round to nearest 0.25
    return rounded.toFixed(2);
}

function noResources() {
    alert('No available resources.');
}

function suggestion(){
    const participationPercent = ((student.schoolsup + student.famsup + student.paid + student.activities)/4)*100;
    if (student.ave < 10 && student.absences > 10 && participationPercent <75){
        return 'At risk: low grade, high absences & low participation';
    } else if(student.ave < 10 && student.absences > 10){
        return 'At risk: low grade & high absences';
    } else if(student.ave < 10  && participationPercent <75){
        return 'Needs grade improvement & improve participation';
    } else if(student.absences > 10 && participationPercent <75){
        return 'Monitor attendance & improve participation';
    } else if(student.ave < 10){
        return 'Needs grade improvement';
    } else if(student.absences > 10){
        return 'Monitor attendance';
    } else if(participationPercent <75){
        return 'Improve participation';
    } else {
        return 'Performing well';}
}

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-green-900 dark:text-green-300">
               Hi, {{ user }}!
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <div class="flex flex-row bg-white dark:bg-gray-800 shadow rounded-2xl p-4 ">
                            <div class="mr-2">
                            <img 
                                src="" 
                                alt="Profile Picture" 
                                class="w-30 h-30 rounded-full object-cover border-2 border-gray-300 dark:border-gray-600"
                            />
                            </div>
                        <!-- <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Profile</h3> -->
                        <div>
                            <!-- <p class="text-sm text-gray-600 dark:text-gray-300">Name: <b>{{ user }}</b></p> -->
                            <p class="text-sm text-gray-600 dark:text-gray-300">Student ID: <b>{{ student.student_id }}</b></p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Program: <b>{{programName}}</b> </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Year Level: <b>3rd Year</b> </p>
                        </div>
                        
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Academic Performance Card -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Academic Performance</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Current GPA: <b>{{convertGrade(student.ave)}}</b></p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Risk Level: 
                            <span 
                                :class="{
                                'text-red-600 font-semibold': student.risk === 'High',
                                'text-yellow-500 font-semibold': student.risk === 'Moderate',
                                'text-green-600 font-semibold': student.risk === 'Low'
                                }"
                            >
                                {{ student.risk }}
                            </span>
                            </p>

                        <p class="text-sm text-gray-600 dark:text-gray-300">Suggestion: <span :class="{
                                                'text-red-600 ':
                                                    suggestion().includes(
                                                        'risk'
                                                    ) ||
                                                    suggestion().includes(
                                                        '&'
                                                    ),
                                                'text-yellow-500':
                                                    suggestion().includes(
                                                        'attendance'
                                                    ) ||
                                                    (suggestion().includes(
                                                        'mprove'
                                                    ) &&
                                                        !suggestion().includes(
                                                            '&'
                                                        )),
                                                'text-green-600':
                                                    suggestion().includes(
                                                        'well'
                                                    ),
                                            }">{{suggestion()}}</span></p>
                       <br>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            <a href="https://mys.mmsu.edu.ph/" target="_blank" class="underline hover:text-blue-600">
                                View my Grades
                            </a>
                        </p>


                    </div>

                    <!-- Upcoming Deadlines / Calendar -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Upcoming Deadlines</h3>
                        <ul class="text-sm text-gray-600 dark:text-gray-300 list-disc ml-5">
                            <li>CMPSC 101 Assignment - May 20</li>
                            <li>SE Project - May 22</li>
                            
                        </ul><br><br>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            <a href="https://mvle4.mmsu.edu.ph/" target="_blank" class="underline hover:text-blue-600">
                                Open MVLE
                            </a>
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">📥 Resources</h3>
                        <ul class="text-sm text-blue-600 dark:text-blue-400 list-disc ml-5">
                            <li><a href="#" @click.prevent="noResources">Download Syllabus</a></li>
                            <li><a href="#" @click.prevent="noResources">Lecture Notes</a></li>
                        </ul>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-4 col-span-1 md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">📢 Announcements</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">📌 Midterms will start on May 25th. Prepare your notes and study materials!</p>
                    </div>




                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* You can add animations or transitions here if needed */
</style>
