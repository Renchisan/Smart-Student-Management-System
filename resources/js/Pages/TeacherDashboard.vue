<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { Bar } from "vue-chartjs";
import { ref, computed, watch } from "vue";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from "chart.js";

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
);

const page = usePage();
const avgStats = page.props.chartData;
const lowestAbsences = page.props.lowestAbsences;
const highestAbsences = page.props.highestAbsences;
const students = page.props.students;
const rowsPerPageOptions = [5, 10, 25];
const rowsPerPage = ref(5);
const currentPage = ref(1);
const sortKey = ref("name");
const sortAsc = ref(true);
const selectedStudents = ref(new Set());
const selectedProgram = ref("");

const filteredStudents = computed(() => {
    if (!selectedProgram.value) {
        return students; // return all if no filter
    }
    return students.filter(student => student.program === selectedProgram.value);
});


const chartData = {
    labels: avgStats.map((item) => item.label),
    datasets: [
        {
            label: "Average Grade",
            data: avgStats.map((item) => item.value),
            backgroundColor: "#4ade80", // green
        },
        {
            label: "Average Absences",
            data: avgStats.map((item) => item.avg_absences),
            backgroundColor: "#f87171", // red
        },
    ],
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: "top", color: "black" },
        title: {
            display: true,
            text: "Grades & Absences Overview by Program",
            color: "black",
            align: "start",
            font: {
                size: 15,
                weight: 500,
            },
        },
    },
};

const showLowest = ref(true);
function toggleAbsences() {
    showLowest.value = !showLowest.value;
}

const toggleSort = (key) => {
    if (sortKey.value === key) {
        sortAsc.value = !sortAsc.value;
    } else {
        sortKey.value = key;
        sortAsc.value = true;
    }
};

const sortOptions = [
    { label: "Name A-Z", key: "name", order: "asc" },
    { label: "Name Z-A", key: "name", order: "desc" },
    { label: "Grade Asc", key: "g_avg", order: "asc", isConverted: true  },
    { label: "Grade Desc", key: "g_avg", order: "desc",isConverted: true  },
    { label: "Attendance % Asc", key: "attendancePercent", order: "asc" },
    { label: "Attendance % Desc", key: "attendancePercent", order: "desc" },
    { label: "Participation % Asc", key: "participationPercent", order: "asc" },
    {
        label: "Participation % Desc",
        key: "participationPercent",
        order: "desc",
    },
    { label: "Risk Asc", key: "recent_risk", order: "asc" },
    { label: "Risk Desc", key: "recent_risk", order: "desc" },
];
const selectedSort = ref(sortOptions[0]);
const sortedStudents = computed(() => {
    return [...filteredStudents.value].sort((a, b) => {
        let valA, valB;

        if (selectedSort.value.key === "attendancePercent") {
            valA = Math.max(0, ((90 - a.absences) / 90) * 100);
            valB = Math.max(0, ((90 - b.absences) / 90) * 100);
        } else if (selectedSort.value.isConverted && selectedSort.value.key === "g_avg") {
            valA = parseFloat(convertGrade(a.g_avg));
            valB = parseFloat(convertGrade(b.g_avg));
        } else if (selectedSort.value.key === "participationPercent") {
            const participation = (s) =>
                ((s.schoolsup + s.famsup + s.paid + s.activities) / 4) * 100;
            valA = participation(a);
            valB = participation(b);
        } else {
            valA = a[selectedSort.value.key];
            valB = b[selectedSort.value.key];
        }

        if (typeof valA === "string") valA = valA.toLowerCase();
        if (typeof valB === "string") valB = valB.toLowerCase();

        if (valA < valB) return selectedSort.value.order === "asc" ? -1 : 1;
        if (valA > valB) return selectedSort.value.order === "asc" ? 1 : -1;
        return 0;
    });
});

const paginatedStudents = computed(() => {
    const start = (currentPage.value - 1) * rowsPerPage.value;
    return sortedStudents.value.slice(start, start + rowsPerPage.value);
});

const totalPages = computed(() =>
    Math.ceil(filteredStudents.value.length / rowsPerPage.value)
);

const toggleSelect = (studentId) => {
    const updated = new Set(selectedStudents.value);
    if (updated.has(studentId)) {
        updated.delete(studentId);
    } else {
        updated.add(studentId);
    }
    selectedStudents.value = updated;
};

const selectAllCurrentPage = () => {
    const updated = new Set(selectedStudents.value);
    paginatedStudents.value.forEach((s) => updated.add(s.student_id));
    selectedStudents.value = updated;
};

const allSelected = computed(() =>
    paginatedStudents.value.every((s) => selectedStudents.value.has(s.student_id))
);


const deselectAllCurrentPage = () => {
    const updated = new Set(selectedStudents.value);
    paginatedStudents.value.forEach((s) => updated.delete(s.student_id));
    selectedStudents.value = updated;
};

const deleteSelected = () => {
    if (selectedStudents.value.size === 0) {
        alert("No students selected.");
        return;
    }

    const ids = Array.from(selectedStudents.value);
    if (
        confirm(
            `Are you sure you want to remove selected student(s)?\n\n${ids.join(", ")}\n\nClick OK to Confirm Removal Request or Cancel to abort.`
        )
    ) {
        alert("Student removal request received. Please allow up to 1 business day for processing.");
        selectedStudents.value.clear();
    }
};

const attendancePercent = (absences) => {
    return Math.max(0, ((90 - absences) / 90) * 100).toFixed(1);
};

const participationPercent = (s) => {
    const total = s.schoolsup + s.famsup + s.paid + s.activities;
    return ((total / 4) * 100).toFixed(1);
};

function convertGrade(g) {
    if (g < 0) g = 0;
    if (g > 19) g = 19;
    const converted = 5 - (g / 19) * 4; // maps 19->1 and 0->5
    const rounded = Math.round(converted * 4) / 4; // round to nearest 0.25
    return rounded.toFixed(2);
}

const addStudent = () => {
    alert("Add student functionality to be implemented");
};

const editStudent = () => {
    alert("Editing student data functionality to be implemented");
};

const deleteStudent = (studentName, studentID) => {
    if (confirm(`Are you sure you want to remove Student ${studentID} ${studentName}?\n\nClick OK to Confirm Removal Request or Cancel to abort.`)) {
        alert(`Student removal request received. Please allow up to 1 business day for processing.`);
    }
};

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

        <div class="pt-6 pb-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <!-- <div class="p-6 text-gray-900 dark:text-gray-100"> -->
                    <!-- <h1 class="text-lg mb-4 py-4 px-6 bg-white shadow-md"> <b>Hi, {{ $page.props.auth.user.name }}!</b></h1> -->
                    <!-- <div id="dashboard" class="p-4 bg-white shadow-md mt-4"> -->
                    <div
                        class="flex flex-col items-top pt-4 px-6 bg-white shadow-md h-[350px]"
                    >
                        <div>
                            <h1 class="text-xl font-bold mb-4">Overview</h1>
                        </div>
                        <div
                            class="flex flex-row justify-between items-top mx-24"
                        >
                            <div class="mx-2">
                                <div class="w-[400px] h-[270px]">
                                    <Bar
                                        :data="chartData"
                                        :options="chartOptions"
                                    />
                                </div>
                            </div>
                            <div class="mx-2">
                                <div class="w-[450px] h-[200px]">
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <h2 class="ml-4 text-md font-bold">
                                            {{
                                                showLowest
                                                    ? "Lowest"
                                                    : "Highest"
                                            }}
                                            Absences Students
                                        </h2>
                                        <button
                                            @click="toggleAbsences"
                                            class="px-2 py-2 text-black text-sm rounded hover:text-green-900 flex items-center justify-center"
                                            :aria-label="
                                                showLowest
                                                    ? 'Show Highest Absences'
                                                    : 'Show Lowest Absences'
                                            "
                                        >
                                            <svg
                                                v-if="showLowest"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <!-- Down Arrow (˅) -->
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M19 9l-7 7-7-7"
                                                />
                                            </svg>

                                            <svg
                                                v-else
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <!-- Up Arrow (˄) -->
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M5 15l7-7 7 7"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <table
                                        class="text-sm w-full table-auto border-collapse text-gray-600"
                                    >
                                        <thead>
                                            <tr
                                                class="bg-gray-100 dark:bg-gray-700"
                                            >
                                                <th
                                                    class="border-y border-gray-300 dark:border-gray-600 px-4 py-2 text-left"
                                                >
                                                    Student Name
                                                </th>
                                                <th
                                                    class="border-y border-gray-300 dark:border-gray-600 px-4 py-2"
                                                >
                                                    Program
                                                </th>
                                                <th
                                                    class="border-y border-gray-300 dark:border-gray-600 px-4 py-2"
                                                >
                                                    Absences
                                                </th>
                                                <th
                                                    class="border-y border-gray-300 dark:border-gray-600 px-4 py-2"
                                                >
                                                    Avg Grade
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="student in showLowest
                                                    ? lowestAbsences
                                                    : highestAbsences"
                                                :key="student.id"
                                                class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-700"
                                            >
                                                <td
                                                    class="border-y border-gray-300 dark:border-gray-600 px-4 py-2 text-left"
                                                >
                                                    {{ student.name }}
                                                </td>
                                                <td
                                                    class="border-y border-gray-300 dark:border-gray-600 px-4 py-2 text-center"
                                                >
                                                    {{ student.program }}
                                                </td>
                                                <td
                                                    class="border-y border-gray-300 dark:border-gray-600 px-4 py-2 text-center"
                                                >
                                                    {{ student.absences }}
                                                </td>
                                                <td
                                                    class="border-y border-gray-300 dark:border-gray-600 px-4 py-2 text-center"
                                                >
                                                    {{
                                                        convertGrade(
                                                            student.g_avg
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- </div> -->
                    <div
                        id="student-list"
                        class="mt-4 pt-4 pb-4 px-6 bg-white shadow-md"
                    >
                        <div class="flex flex-row justify-between items-center">
                            <div>
                                <h1 class="text-xl font-bold">Students</h1>
                                <p class="mb-4 text-sm">
                                    <b></b>adding and deleting student data
                                    requires due process, an email will be sent
                                    to confirm action*
                                </p>
                            </div>

                            <!-- <div class="p-6 bg-white rounded shadow-md"> -->
                            <!-- <div class="flex justify-end mb-4"> -->

                            <!-- <div class="flex justify-end ">
                                    <label class="mr-2 font-semibold">Rows per page:</label>
                                    <select v-model="rowsPerPage" class="border rounded px-8 py-1">
                                    <option v-for="option in rowsPerPageOptions" :key="option" :value="option">{{ option }}</option>
                                    </select>
                                </div>
                                </div> -->

                            <div class="flex justify-end items-center mb-4">
                                <div>
                                    <label
                                        for="sort"
                                        class="mr-2 font-semibold self-center"
                                        >Sort by:</label
                                    >
                                    <select
                                        id="sort"
                                        v-model="selectedSort"
                                        class="border rounded-full px-3 py-1 text-sm h-8 w-36"
                                    >
                                        <option
                                            v-for="option in sortOptions"
                                            :key="option.label"
                                            :value="option"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <button
                                        @click="addStudent"
                                        class="ml-2 text-sm h-5 w-15"
                                    >
                                        <svg
                                            width="25px"
                                            height="25px"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <g id="Edit">
                                                <path
                                                    id="Vector"
                                                    d="M8 12H12M12 12H16M12 12V16M12 12V8M12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21Z"
                                                    stroke="#000000"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </g>
                                        </svg>
                                    </button>
                                </div>
                                <div>
                                    <button
                                        @click="deleteSelected"
                                        :disabled="selectedStudents.size === 0"
                                        class="mr-2 text-sm h-5 w-15"
                                    >
                                        <svg
                                            width="25px"
                                            height="25px"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M10 12V17"
                                                stroke="#000000"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path
                                                d="M14 12V17"
                                                stroke="#000000"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path
                                                d="M4 7H20"
                                                stroke="#000000"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path
                                                d="M6 10V18C6 19.6569 7.34315 21 9 21H15C16.6569 21 18 19.6569 18 18V10"
                                                stroke="#000000"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path
                                                d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7H9V5Z"
                                                stroke="#000000"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <table
                            class="min-w-full border border-gray-300 rounded overflow-hidden mx-auto mb-3"
                        >
                            <thead class="bg-gray-100">
                                <tr>
                                    <th
                                        class="p-2 border-b cursor-pointer text-left"
                                        @click="toggleSort('name')"
                                    >
                                        <input
                                            type="checkbox"
                                            class="form-checkbox text-gray-600 rounded mr-1 w-5 h-5 border-gray-300 focus:ring-gray-400"  
                                             :checked="allSelected"
                                            @change="
                                                $event.target.checked
                                                    ? selectAllCurrentPage()
                                                    : deselectAllCurrentPage()
                                            "
                                        />
                                        Name
                                        <!-- <span v-if="sortKey === 'name'">{{ sortAsc ? '▲' : '▼' }}</span> -->
                                    </th>
                                    <th
                                        class="p-2 border-b cursor-pointer"
                                        @click="toggleSort('program')"
                                    >
                                        Program
                                        <!-- <span v-if="sortKey === 'g_avg'">{{ sortAsc ? '▲' : '▼' }}</span> -->
                                    </th>
                                    <th
                                        class="p-2 border-b cursor-pointer"
                                        @click="toggleSort('g_avg')"
                                    >
                                        Average Grade
                                        <!-- <span v-if="sortKey === 'g_avg'">{{ sortAsc ? '▲' : '▼' }}</span> -->
                                    </th>
                                    <th
                                        class="p-2 border-b cursor-pointer"
                                        @click="toggleSort('attendance')"
                                    >
                                        Attendance %
                                        <!-- <span v-if="sortKey === 'attendance'">{{ sortAsc ? '▲' : '▼' }}</span> -->
                                    </th>
                                    <th
                                        class="p-2 border-b cursor-pointer"
                                        @click="toggleSort('participation')"
                                    >
                                        Participation %
                                        <!-- <span v-if="sortKey === 'participation'">{{ sortAsc ? '▲' : '▼' }}</span> -->
                                    </th>
                                    <th @click="toggleSort('risk')">
                                        Risk Prediction
                                        <!-- <span v-if="sortKey === 'risk'">{{ sortAsc ? '▲' : '▼' }}</span> -->
                                    </th>
                                    <th class="border-t border-b px-4 py-2">
                                        Suggestion
                                    </th>
                                    <th class="border-t border-b px-4 py-2">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="student in paginatedStudents"
                                    :key="student.student_id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="p-2 border-b items-center">
                                        <input
                                            type="checkbox"
                                            class="form-checkbox text-gray-600 rounded w-5 h-5 border-gray-300 focus:ring-gray-400"  

                                            :checked="
                                                selectedStudents.has(
                                                    student.student_id
                                                )
                                            "
                                            @change="
                                                toggleSelect(student.student_id)
                                            "
                                        />
                                        <span class="pl-2">{{
                                            student.name
                                        }}</span>
                                    </td>
                                    <td class="p-2 border-b text-center">
                                        {{ student.program }}
                                    </td>
                                    <td class="p-2 border-b text-center">
                                        {{
                                            convertGrade(student.g_avg)
                                        }}
                                    </td>
                                    <td class="p-2 border-b text-center">
                                        {{
                                            attendancePercent(student.absences)
                                        }}%
                                    </td>
                                    <td class="p-2 border-b text-center">
                                        {{ participationPercent(student) }}%
                                    </td>
                                    <td class="p-2 border-b text-center">
                                        {{ student.recent_risk ?? "N/A" }}
                                    </td>
                                    <td class="p-2 border-b text-left">
                                        <span
                                            :class="{
                                                'text-red-600 ':
                                                    student.suggestion.includes(
                                                        'risk'
                                                    ) ||
                                                    student.suggestion.includes(
                                                        '&'
                                                    ),
                                                'text-yellow-500':
                                                    student.suggestion.includes(
                                                        'attendance'
                                                    ) ||
                                                    (student.suggestion.includes(
                                                        'mprove'
                                                    ) &&
                                                        !student.suggestion.includes(
                                                            '&'
                                                        )),
                                                'text-green-600':
                                                    student.suggestion.includes(
                                                        'well'
                                                    ),
                                            }"
                                        >
                                            {{ student.suggestion }}
                                        </span>
                                    </td>
                                    <td class="p-2 border-b text-center">
                                        <button
                                            @click="editStudent"
                                            class="text-sm h-5 w-15"
                                        >
                                            <svg
                                                width="25px"
                                                height="25px"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    clip-rule="evenodd"
                                                    d="M21.1213 2.70705C19.9497 1.53548 18.0503 1.53547 16.8787 2.70705L15.1989 4.38685L7.29289 12.2928C7.16473 12.421 7.07382 12.5816 7.02986 12.7574L6.02986 16.7574C5.94466 17.0982 6.04451 17.4587 6.29289 17.707C6.54127 17.9554 6.90176 18.0553 7.24254 17.9701L11.2425 16.9701C11.4184 16.9261 11.5789 16.8352 11.7071 16.707L19.5556 8.85857L21.2929 7.12126C22.4645 5.94969 22.4645 4.05019 21.2929 2.87862L21.1213 2.70705ZM18.2929 4.12126C18.6834 3.73074 19.3166 3.73074 19.7071 4.12126L19.8787 4.29283C20.2692 4.68336 20.2692 5.31653 19.8787 5.70705L18.8622 6.72357L17.3068 5.10738L18.2929 4.12126ZM15.8923 6.52185L17.4477 8.13804L10.4888 15.097L8.37437 15.6256L8.90296 13.5112L15.8923 6.52185ZM4 7.99994C4 7.44766 4.44772 6.99994 5 6.99994H10C10.5523 6.99994 11 6.55223 11 5.99994C11 5.44766 10.5523 4.99994 10 4.99994H5C3.34315 4.99994 2 6.34309 2 7.99994V18.9999C2 20.6568 3.34315 21.9999 5 21.9999H16C17.6569 21.9999 19 20.6568 19 18.9999V13.9999C19 13.4477 18.5523 12.9999 18 12.9999C17.4477 12.9999 17 13.4477 17 13.9999V18.9999C17 19.5522 16.5523 19.9999 16 19.9999H5C4.44772 19.9999 4 19.5522 4 18.9999V7.99994Z"
                                                    fill="#000000"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteStudent(student.name, student.student_id)"
                                            
                                            
                                            class="mx-2 text-sm h-5 w-15"
                                        >
                                            <svg
                                                width="25px"
                                                height="25px"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M10 12V17"
                                                    stroke="#000000"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                                <path
                                                    d="M14 12V17"
                                                    stroke="#000000"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                                <path
                                                    d="M4 7H20"
                                                    stroke="#000000"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                                <path
                                                    d="M6 10V18C6 19.6569 7.34315 21 9 21H15C16.6569 21 18 19.6569 18 18V10"
                                                    stroke="#000000"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                                <path
                                                    d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7H9V5Z"
                                                    stroke="#000000"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination controls -->
                        <div class="flex justify-between items-center mb-4 mt-8">
                          <div class="flex flex-row">
                            <div class="">
                                <label class="mr-2 font-semibold"
                                    >Rows per page:</label
                                >
                                <select
                                    v-model="rowsPerPage"
                                    class="border rounded-full px-4 py-1 h-8 w-16"
                                >
                                    <option
                                        v-for="option in rowsPerPageOptions"
                                        :key="option"
                                        :value="option"
                                    >
                                        {{ option }}
                                    </option>
                                </select>
                            </div>
                            <div class="ml-4 mb-4">
                                <label
                                    for="programFilter"
                                    class="mr-2 font-semibold"
                                    >Filter by Program:</label
                                >
                                <select
                                    id="programFilter"
                                    v-model="selectedProgram"
                                    class="border rounded-full px-2 py-1 w-16"
                                >
                                    <option value="">All</option>
                                    <option value="CS">CS</option>
                                    <option value="IT">IT</option>
                                </select>
                            </div>
                            </div>
                            <div>
                                <div
                                    class="mt-4 flex justify-between items-center"
                                >
                                    <button
                                        @click="
                                            currentPage > 1 &&
                                                (currentPage -= 1)
                                        "
                                        :disabled="currentPage === 1"
                                        class="px-3 py-1 mx-2 rounded bg-gray-200 disabled:opacity-50"
                                    >
                                        <
                                    </button>
                                    <span>
                                        {{ currentPage }} of
                                        {{ totalPages }}</span
                                    >
                                    <button
                                        @click="
                                            currentPage < totalPages &&
                                                (currentPage += 1)
                                        "
                                        :disabled="currentPage === totalPages"
                                        class="px-3 mx-2 py-1 rounded bg-gray-200 disabled:opacity-50"
                                    >
                                        >
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- </div> -->
                    </div>
                </div>
            </div>
            <!-- </div> -->
        </div>
    </AuthenticatedLayout>
</template>
