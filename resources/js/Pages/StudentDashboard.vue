<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
const page = usePage();

const user = page.props.auth.user.name;

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
                                src="https://via.placeholder.com/80" 
                                alt="Profile Picture" 
                                class="w-30 h-30 rounded-full object-cover border-2 border-gray-300 dark:border-gray-600"
                            />
                            </div>
                        <!-- <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Profile</h3> -->
                        <div>
                            <!-- <p class="text-sm text-gray-600 dark:text-gray-300">Name: <b>{{ user }}</b></p> -->
                            <p class="text-sm text-gray-600 dark:text-gray-300">Student ID: <b>202012345</b></p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Program: <b>BS Computer Science</b> </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Year Level: <b>3th Year</b> </p>
                        </div>
                        
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Academic Performance Card -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">📊 Academic Performance</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Current GPA: <b>2.50</b></p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Risk Level: <span class="text-yellow-500 font-semibold">Moderate</span></p>
                        <p class="text-sm text-gray-600 dark:text-gray-300"><u>View my Grades</u></p>

                    </div>

                    <!-- Attendance Card
                    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">📅 Attendance</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Total Attendance: <b>89%</b></p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Recent Absences: 2 classes</p>
                        <p></p>
                    </div> -->

                    <!-- Upcoming Deadlines / Calendar -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">⏰ Upcoming Deadlines</h3>
                        <ul class="text-sm text-gray-600 dark:text-gray-300 list-disc ml-5">
                            <li>Math Assignment - May 20</li>
                            <li>Science Project - May 22</li>
                        </ul>
                        <p class="text-sm text-gray-600 dark:text-gray-300"><u>Open VLE</u></p>
                    </div>
                    <!-- Resources Download -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">📥 Resources</h3>
                        <ul class="text-sm text-blue-600 dark:text-blue-400 list-disc ml-5">
                            <li><a href="#">Download Syllabus</a></li>
                            <li><a href="#">Lecture Notes</a></li>
                        </ul>
                    </div>
                    <!-- Announcements -->
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
