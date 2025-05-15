<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title, Tooltip, Legend,
  BarElement, CategoryScale, LinearScale
} from 'chart.js'

ChartJS.register(
  Title, Tooltip, Legend,
  BarElement, CategoryScale, LinearScale
);

const chartData = {
  labels: ['Math', 'Science', 'English'],
  datasets: [
    {
      label: 'Average Grade',
      data: [85, 90, 78],
      backgroundColor: '#4ade80', // green-400
    }
  ]
}

const chartOptions = {
  responsive: true,
  plugins: {
    legend: { position: 'top' },
    title: { display: true, text: 'Grades Overview' }
  }
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <!-- <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Teach Dashboard
            </h2>
        </template> -->

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h1 class="text-lg"> <b>Hi, {{ $page.props.auth.user.name }}!</b></h1>
                        <div id="dashboard" class="p-4 bg-white shadow-md mt-4">
                            Overview
                            <div class="mx-4">
                                percentage
                            </div>
                            <div class="flex flex-row justify-center text-center mx-4">
                                <div class="flex-1" >
                                    <div class="mb-6">
                                        <Bar :data="chartData" :options="chartOptions" />
                                    </div>
                                </div>
                                <div class="flex-1">top</div>
                            </div>
                        </div>
                        <div id="student-list"  class="p-4 bg-white shadow-md mt-4">
                            Students 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
